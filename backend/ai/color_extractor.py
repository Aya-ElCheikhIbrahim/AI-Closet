import argparse
import json
from PIL import Image
import numpy as np
from sklearn.cluster import KMeans

BASIC_COLORS = {
    "black": (0, 0, 0),
    "white": (255, 255, 255),
    "gray": (128, 128, 128),

    "cream": (255, 253, 208),
    "beige": (245, 245, 220),
    "camel": (193, 154, 107),
    "brown": (139, 69, 19),

    "red": (255, 0, 0),
    "burgundy": (128, 0, 32),

    "pink": (255, 192, 203),
    "dusty_rose": (188, 143, 143),

    "orange": (255, 165, 0),
    "peach": (255, 218, 185),

    "yellow": (255, 255, 0),
    "mustard": (255, 219, 88),

    "green": (0, 128, 0),
    "olive": (128, 128, 0),
    "sage": (188, 184, 138),

    "blue": (0, 0, 255),
    "navy": (0, 0, 128),
    "sky_blue": (135, 206, 235),
    "teal": (0, 128, 128),

    "purple": (128, 0, 128),
    "lavender": (230, 230, 250)
}


def closest_color_name(rgb):
    min_distance = float("inf")
    closest_name = None

    for name, color_rgb in BASIC_COLORS.items():
        distance = sum((int(rgb[i]) - color_rgb[i]) ** 2 for i in range(3)) ** 0.5

        if distance < min_distance:
            min_distance = distance
            closest_name = name

    return closest_name


def classify_color(rgb):
    r, g, b = [int(x) for x in rgb]

    # black
    if r < 45 and g < 45 and b < 45:
        return "black"

    # white / cream / beige family
    if r > 240 and g > 240 and b > 235:
        return "white"

    if r > 220 and g > 210 and b > 180:
        return "cream"

    if r > 185 and g > 160 and b > 120:
        return "beige"

    # gray only if RGB values are very close
    if abs(r - g) < 15 and abs(g - b) < 15 and abs(r - b) < 15:
        if r > 180:
            return "gray"
        return "gray"

    # red / burgundy
    if r > 90 and g < 80 and b < 90:
        if r < 160:
            return "burgundy"
        return "red"

    return closest_color_name((r, g, b))


def extract_colors(image_path, n_colors=4):
    image = Image.open(image_path).convert("RGBA")
    image = image.resize((250, 250))

    pixels = np.array(image)

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
        name = classify_color(rgb)

        colors.append({
            "name": name,
            "rgb": rgb.tolist(),
            "percentage": round(float(percentage), 3)
        })

    # remove duplicate color names while keeping order
    unique_colors = []
    seen = set()

    for color in colors:
        if color["name"] not in seen:
            unique_colors.append(color)
            seen.add(color["name"])

    primary = unique_colors[0]["name"]
    secondary = None

    if len(unique_colors) > 1 and unique_colors[1]["percentage"] >= 0.12:
        secondary = unique_colors[1]["name"]

    return {
        "success": True,
        "primaryColor": primary,
        "secondaryColor": secondary,
        "colors": unique_colors
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