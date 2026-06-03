import argparse
import json
from pathlib import Path

from PIL import Image
import torch
from transformers import CLIPProcessor, CLIPModel

BASE_DIR = Path(__file__).resolve().parent
MODEL_PATH = BASE_DIR / "models" / "fashionclip"

CATEGORIES = [
    "t-shirt",
    "shirt",
    "blouse",
    "hoodie",
    "sweater",
    "jacket",
    "coat",
    "dress",
    "skirt",
    "jeans",
    "pants",
    "shorts",
    "shoes",
    "bag",
    "accessory"
]

processor = CLIPProcessor.from_pretrained(str(MODEL_PATH))
model = CLIPModel.from_pretrained(str(MODEL_PATH))
model.eval()

def classify_clothing(image_path):
    image = Image.open(image_path).convert("RGB")

    labels = [f"a photo of a {category}" for category in CATEGORIES]

    inputs = processor(
        text=labels,
        images=image,
        return_tensors="pt",
        padding=True
    )

    with torch.no_grad():
        outputs = model(**inputs)
        probs = outputs.logits_per_image.softmax(dim=1)[0]

    best_index = probs.argmax().item()

    return {
        "success": True,
        "category": CATEGORIES[best_index],
        "confidence": float(probs[best_index])
    }

if __name__ == "__main__":
    parser = argparse.ArgumentParser()
    parser.add_argument("--file", required=True)
    args = parser.parse_args()

    try:
        result = classify_clothing(args.file)
        print(json.dumps(result))
    except Exception as e:
        print(json.dumps({
            "success": False,
            "error": str(e)
        }))