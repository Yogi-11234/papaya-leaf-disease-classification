import os
import sys
from fastapi import FastAPI, File, UploadFile, HTTPException
from fastapi.responses import JSONResponse
from pydantic import BaseModel
from PIL import Image
import torch
import torch.nn.functional as F
from torchvision import transforms
from model import PapayaLeafCNN, CLASS_NAMES, count_parameters
from dotenv import load_dotenv

# Load env variables
load_dotenv()

MODEL_PATH = os.getenv("MODEL_PATH", "./models/best_overall_model.pth")
DEVICE = os.getenv("DEVICE", "cpu")

# Visual instruction block if model is missing
if not os.path.exists(MODEL_PATH):
    print("=" * 80)
    print(" ERROR: FILE MODEL UTAMA TIDAK DITEMUKAN!")
    print(f" Harap letakkan file weight model terbaik ('best_overall_model.pth') di path berikut:")
    print(f" Path target: {os.path.abspath(MODEL_PATH)}")
    print("=" * 80)
    print("FastAPI Service setup dihentikan.")
    sys.exit(1)

# Initialize model
try:
    model = PapayaLeafCNN(num_classes=5)
    checkpoint = torch.load(MODEL_PATH, map_location=DEVICE)
    
    # Check if checkpoint is a dict with state_dict or direct state_dict
    if isinstance(checkpoint, dict) and 'model_state_dict' in checkpoint:
        model.load_state_dict(checkpoint['model_state_dict'])
    elif isinstance(checkpoint, dict) and 'state_dict' in checkpoint:
        model.load_state_dict(checkpoint['state_dict'])
    else:
        model.load_state_dict(checkpoint)
        
    model.to(DEVICE)
    model.eval()
    
    # Calculate parameter count
    total_params = count_parameters(model)
    print(f"✅ Model successfully loaded from {MODEL_PATH}")
    print(f"   Trainable Parameters: {total_params:,}")
except Exception as e:
    print(f"❌ Error loading model: {str(e)}")
    sys.exit(1)

app = FastAPI(title="PapayaLeafCNN Inference Service")

# Image transforms - Identical to training pipeline (ImageNet mean & std)
preprocess = transforms.Compose([
    transforms.Resize((224, 224)),
    transforms.ToTensor(),
    transforms.Normalize(
        mean=[0.485, 0.456, 0.406],
        std=[0.229, 0.224, 0.225]
    )
])

@app.get("/health")
def health_check():
    """
    Sanity check endpoint that verifies the model is loaded
    and has exactly 420,389 trainable parameters.
    """
    expected_params = 420389
    actual_params = count_parameters(model)
    
    if actual_params != expected_params:
        return JSONResponse(
            status_code=500,
            content={
                "status": "error",
                "message": f"Sanity check failed: Expected {expected_params} parameters, but got {actual_params}",
                "model_loaded": True,
                "trainable_parameters": actual_params
            }
        )
        
    return {
        "status": "ok",
        "model_loaded": True,
        "trainable_parameters": actual_params
    }

@app.post("/predict")
async def predict(file: UploadFile = File(...)):
    """
    Predicts papaya leaf disease from upload image.
    """
    if not file.content_type.startswith("image/"):
        raise HTTPException(status_code=400, detail="Uploaded file is not an image.")
        
    try:
        # Load image using PIL
        image = Image.open(file.file)
        if image.mode != "RGB":
            image = image.convert("RGB")
            
        # Apply preprocessing
        input_tensor = preprocess(image)
        input_batch = input_tensor.unsqueeze(0).to(DEVICE) # Create a mini-batch of size 1
        
        # Run inference
        with torch.no_grad():
            outputs = model(input_batch)
            probabilities = F.softmax(outputs, dim=1)[0]
            
        # Format prediction probabilities
        prob_dict = {
            CLASS_NAMES[i]: float(probabilities[i])
            for i in range(len(CLASS_NAMES))
        }
        
        # Get class with highest probability
        pred_idx = torch.argmax(probabilities).item()
        pred_label = CLASS_NAMES[pred_idx]
        confidence = float(probabilities[pred_idx])
        
        return {
            "label_prediksi": pred_label,
            "confidence": confidence,
            "probabilities": prob_dict
        }
        
    except Exception as e:
        return JSONResponse(
            status_code=500,
            content={"error": f"Prediction failed: {str(e)}"}
        )
