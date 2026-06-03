import sys
import json
import argparse
from pathlib import Path

try:
    from rembg import remove
    from PIL import Image, ImageFilter
except ImportError:
    print(json.dumps({
        "success": False,
        "error": "Missing dependencies. Run: pip install 'rembg[cpu]' pillow"
    }))
    sys.exit(1)


SUPPORTED_FORMATS = {".jpg", ".jpeg", ".png", ".webp", ".bmp"}

CANVAS_SIZE = (1000, 1000)
PADDING = 120


def remove_background(image: Image.Image, smooth_edges: bool = True) -> Image.Image:
    if image.mode != "RGBA":
        image = image.convert("RGBA")

    result = remove(image)

    if smooth_edges:
        result = clean_edges(result)

    return result


def clean_edges(image: Image.Image) -> Image.Image:
    r, g, b, alpha = image.split()
    alpha = alpha.filter(ImageFilter.SMOOTH_MORE)
    return Image.merge("RGBA", (r, g, b, alpha))


def center_on_white_canvas(image: Image.Image) -> Image.Image:
    canvas = Image.new("RGBA", CANVAS_SIZE, (255, 255, 255, 255))

    item = image.copy()
    item.thumbnail(
        (CANVAS_SIZE[0] - PADDING, CANVAS_SIZE[1] - PADDING),
        Image.LANCZOS
    )

    x = (CANVAS_SIZE[0] - item.width) // 2
    y = (CANVAS_SIZE[1] - item.height) // 2

    canvas.paste(item, (x, y), item)

    return canvas


def process_file(input_path: Path, output_dir: Path):
    if not input_path.exists():
        raise FileNotFoundError(f"File not found: {input_path}")

    if input_path.suffix.lower() not in SUPPORTED_FORMATS:
        raise ValueError(f"Unsupported image format: {input_path.suffix}")

    output_dir.mkdir(parents=True, exist_ok=True)

    original = Image.open(input_path)

    result = remove_background(original)

    transparent_path = output_dir / f"{input_path.stem}_nobg.png"
    result.save(transparent_path, "PNG")

    white_canvas = center_on_white_canvas(result)
    white_path = output_dir / f"{input_path.stem}_white.jpg"
    white_canvas.convert("RGB").save(white_path, "JPEG", quality=95)

    return {
        "transparent_path": str(transparent_path),
        "white_path": str(white_path)
    }


def main():
    parser = argparse.ArgumentParser(
        description="Remove background from a clothing image."
    )

    parser.add_argument("--file", "-f", required=True, type=str)
    parser.add_argument("--output", "-o", required=True, type=str)

    args = parser.parse_args()

    try:
        result = process_file(
            input_path=Path(args.file),
            output_dir=Path(args.output)
        )

        print(json.dumps({
            "success": True,
            "transparent_path": result["transparent_path"],
            "white_path": result["white_path"]
        }))

    except Exception as e:
        print(json.dumps({
            "success": False,
            "error": str(e)
        }))
        sys.exit(1)


if __name__ == "__main__":
    main()