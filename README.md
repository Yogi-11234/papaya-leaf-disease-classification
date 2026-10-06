# Papaya Leaf Disease Classification & AI Consultation

Web-based system for classifying papaya leaf diseases using a custom Convolutional Neural Network (CNN) and providing AI-assisted consultation based on the classification result.

This project was developed as an undergraduate thesis with a case study in **Desa Cibodas, Kabupaten Bandung Barat**.

## Features

- Papaya leaf image classification
- Image capture/upload from camera or device
- Classification history
- Model performance dashboard
- AI chatbot consultation based on classification results
- Responsive web interface

## Dataset

The dataset contains **422 original papaya leaf images** collected using a Samsung A05s camera.

Five classification classes:

- Anthracnose
- Bacterial Spot
- Curl
- Ring Spot
- Healthy

## Model

The system uses **PapayaLeafCNN**, a custom CNN designed from scratch without pre-trained weights.

Main characteristics:

- Input size: 224 × 224 pixels
- 5 convolutional blocks
- Residual connections
- Global Average Pooling
- Dropout
- 420,389 trainable parameters
- 5-Fold Stratified Group Cross Validation

### Training

- Optimizer: AdamW
- Learning Rate: 0.001
- Batch Size: 32
- Label Smoothing: 0.05
- ReduceLROnPlateau Scheduler
- Early Stopping
- Mixed Precision Training (AMP)

## Results

Average 5-Fold Cross Validation results:

| Metric | Result |
|---|---:|
| Accuracy | 77.02% ± 2.69% |
| Precision | 77.27% ± 2.94% |
| Recall | 76.97% ± 2.31% |
| F1-Score | 76.65% ± 2.48% |

Best performance was achieved on **Fold 5 with 80.95% accuracy**.

## Technology Stack

**Machine Learning**
- Python
- PyTorch
- Albumentations
- scikit-learn

**Web Application**
- Laravel 11
- PHP 8.2
- Tailwind CSS
- PostgreSQL

**Inference & AI**
- FastAPI
- Guzzle HTTP
- Groq API
- Llama 3.3 70B

## Project Structure

```text
├── app/
├── database/
├── resources/
├── routes/
├── public/
├── fastapi_service/
├── tests/
├── composer.json
└── package.json
```

## Installation

```bash
git clone https://github.com/Yogi-11234/papaya-leaf-disease-classification.git
cd papaya-leaf-disease-classification

composer install
npm install

cp .env.example .env
php artisan key:generate

php artisan migrate
php artisan serve
```

Run the FastAPI inference service separately according to the configuration in `fastapi_service`.

## Research

This project is part of an undergraduate thesis focusing on the implementation of a custom CNN for papaya leaf disease classification using a locally collected dataset.

For research details, methodology, architecture, and evaluation results, see the thesis documentation.
