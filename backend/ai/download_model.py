from transformers import CLIPModel, CLIPProcessor

MODEL_NAME = "patrickjohncyh/fashion-clip"

model = CLIPModel.from_pretrained(MODEL_NAME)
processor = CLIPProcessor.from_pretrained(MODEL_NAME)

model.save_pretrained("./models/fashionclip")
processor.save_pretrained("./models/fashionclip")

print("FashionCLIP downloaded successfully.")