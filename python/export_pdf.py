#!/usr/bin/env python3
"""Export a public resume or reflection URL through Chromium's print engine."""
from argparse import ArgumentParser
from pathlib import Path
from playwright.sync_api import sync_playwright

parser = ArgumentParser()
parser.add_argument("url", help="Document URL, for example http://localhost:8000/?page=resume")
parser.add_argument("output", help="Destination PDF path")
args = parser.parse_args()
destination = Path(args.output).expanduser().resolve()
destination.parent.mkdir(parents=True, exist_ok=True)

with sync_playwright() as playwright:
    browser = playwright.chromium.launch()
    page = browser.new_page()
    page.goto(args.url, wait_until="networkidle")
    page.pdf(path=str(destination), format="A4", print_background=True, prefer_css_page_size=True)
    browser.close()
print(f"Created {destination}")
