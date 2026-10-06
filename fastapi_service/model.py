import torch
import torch.nn as nn
import torch.nn.functional as F

# Urutan kelas WAJIB sama persis dengan urutan output model saat training
CLASS_NAMES = ["Anthracnose", "BacterialSpot", "Curl", "RingSpot", "Healthy"]

class ResidualBlock(nn.Module):
    """
    Blok residual sederhana (2x Conv3x3 + BN + ReLU) dengan skip connection.
    """
    def __init__(self, channels: int):
        super().__init__()
        self.conv1 = nn.Conv2d(channels, channels, kernel_size=3, padding=1, bias=False)
        self.bn1 = nn.BatchNorm2d(channels)
        self.conv2 = nn.Conv2d(channels, channels, kernel_size=3, padding=1, bias=False)
        self.bn2 = nn.BatchNorm2d(channels)

    def forward(self, x: torch.Tensor) -> torch.Tensor:
        identity = x
        out = F.relu(self.bn1(self.conv1(x)), inplace=True)
        out = self.bn2(self.conv2(out))
        out += identity
        out = F.relu(out, inplace=True)
        return out


class PapayaLeafCNN(nn.Module):
    """
    CNN kustom (from-scratch, tanpa pretrained backbone) dengan total parameter 420.389.
    """
    def __init__(self, num_classes: int = 5, dropout_p: float = 0.5):
        super().__init__()

        # ---- Stage 1 ----
        self.conv1 = nn.Conv2d(3, 32, kernel_size=3, padding=1)
        self.bn1 = nn.BatchNorm2d(32)
        self.conv2 = nn.Conv2d(32, 32, kernel_size=3, padding=1)
        self.bn2 = nn.BatchNorm2d(32)
        self.pool1 = nn.MaxPool2d(2)

        # ---- Stage 2 ----
        self.conv3 = nn.Conv2d(32, 64, kernel_size=3, padding=1)
        self.bn3 = nn.BatchNorm2d(64)
        self.conv4 = nn.Conv2d(64, 64, kernel_size=3, padding=1)
        self.bn4 = nn.BatchNorm2d(64)
        self.pool2 = nn.MaxPool2d(2)

        # ---- Residual Block 1 (Stage 3) ----
        self.res1 = ResidualBlock(64)
        self.pool3 = nn.MaxPool2d(2)

        # ---- Residual Block 2 (Stage 4) ----
        self.res2 = ResidualBlock(64)
        self.pool4 = nn.MaxPool2d(2)

        # ---- Stage 5 ----
        self.conv5 = nn.Conv2d(64, 128, kernel_size=3, padding=1)
        self.bn5 = nn.BatchNorm2d(128)
        self.gap = nn.AdaptiveAvgPool2d(1)

        # ---- Classifier Head ----
        self.fc1 = nn.Linear(128, 512)
        self.dropout1 = nn.Dropout(dropout_p)
        self.fc2 = nn.Linear(512, 128)
        self.dropout2 = nn.Dropout(dropout_p)
        self.fc3 = nn.Linear(128, num_classes)

    def forward(self, x: torch.Tensor) -> torch.Tensor:
        x = F.relu(self.bn1(self.conv1(x)), inplace=True)
        x = F.relu(self.bn2(self.conv2(x)), inplace=True)
        x = self.pool1(x)

        x = F.relu(self.bn3(self.conv3(x)), inplace=True)
        x = F.relu(self.bn4(self.conv4(x)), inplace=True)
        x = self.pool2(x)

        x = self.res1(x)
        x = self.pool3(x)

        x = self.res2(x)
        x = self.pool4(x)

        x = F.relu(self.bn5(self.conv5(x)), inplace=True)
        x = self.gap(x)
        x = torch.flatten(x, 1)

        x = F.relu(self.fc1(x), inplace=True)
        x = self.dropout1(x)
        x = F.relu(self.fc2(x), inplace=True)
        x = self.dropout2(x)
        x = self.fc3(x)
        return x


def count_parameters(model: nn.Module) -> int:
    """Hitung jumlah parameter trainable dari sebuah model."""
    return sum(p.numel() for p in model.parameters() if p.requires_grad)
