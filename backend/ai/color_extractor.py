import argparse
import json
from PIL import Image
import numpy as np
from sklearn.cluster import KMeans

BASIC_COLORS = {
    "black": (0, 0, 0),
    "white": (255, 255, 255),
    "gray": (128, 128, 128),

    # neutrals
    "cream": (255, 253, 208),
    "ivory": (255, 255, 240),
    "beige": (245, 245, 220),
    "tan": (210, 180, 140),
    "camel": (193, 154, 107),
    "khaki": (195, 176, 145),
    "taupe": (139, 133, 137),
    "brown": (139, 69, 19),

    # reds
    "red": (255, 0, 0),
    "burgundy": (128, 0, 32),
    "maroon": (128, 0, 0),

    # pinks
    "pink": (255, 192, 203),
    "rose": (255, 102, 204),
    "dusty_rose": (188, 143, 143),

    # oranges
    "orange": (255, 165, 0),
    "coral": (255, 127, 80),
    "peach": (255, 218, 185),

    # yellows
    "yellow": (255, 255, 0),
    "mustard": (255, 219, 88),
    "gold": (255, 215, 0),

    # greens
    "green": (0, 128, 0),
    "olive": (128, 128, 0),
    "sage": (188, 184, 138),
    "mint": (152, 255, 152),
    "emerald": (80, 200, 120),

    # blues
    "blue": (0, 0, 255),
    "navy": (0, 0, 128),
    "sky_blue": (135, 206, 235),
    "teal": (0, 128, 128),
    "turquoise": (64, 224, 208),

    # purples
    "purple": (128, 0, 128),
    "lavender": (230, 230, 250),
    "lilac": (200, 162, 200),

    # metallics
    "silver": (192, 192, 192),
    "bronze": (205, 127, 50)
}

def closest_color_name(rgb):
    min_distance = float("inf")
    closest_name = None

    for name, color_rgb in BASIC_COLORS.items():
        distance = sum((rgb[i] - color_rgb[i]) ** 2 for i in range(3)) ** 0.5

        if distance < min_distance:
            min_distance = distance
            closest_name = name

    return closest_name

def extract_colors(image_path, n_colors=3):
    image = Image.open(image_path).convert("RGBA")
    image = image.resize((200, 200))

    pixels = np.array(image)

    # keep only visible clothing pixels
    visible_pixels = pixels[pixels[:, :, 3] > 20]

    if len(visible_pixels) == 0:
        return {
            "success": False,
            "error": "No visible pixels found"
        }

    rgb_pixels = visible_pixels[:, :3]

    kmeans = KMeans(n_clusters=n_colors, random_state=42, n_init=10)
    kmeans.fit(rgb_pixels)

    counts = np.bincount(kmeans.labels_)
    total = counts.sum()

    colors = []

    for index in counts.argsort()[::-1]:
        rgb = kmeans.cluster_centers_[index].astype(int)
        percentage = counts[index] / total

        colors.append({
            "name": closest_color_name(rgb),
            "rgb": rgb.tolist(),
            "percentage": round(float(percentage), 3)
        })

    primary = colors[0]["name"]
    secondary = None

    if len(colors) > 1 and colors[1]["percentage"] >= 0.15:
        secondary = colors[1]["name"]

    return {
        "success": True,
        "primaryColor": primary,
        "secondaryColor": secondary,
        "colors": colors
    }

if __name__ == "__main__":
    parser = argparse.ArgumentParser()
    parser.add_argument("--file", required=True)

    args = parser.parse_args()

    try:
        result = extract_colors(args.file)
        print(json.dumps(result))
    except Exception as e:
        print(json.dumps({
            "success": False,
            "error": str(e)
        })) 