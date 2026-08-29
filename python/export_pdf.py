#!/usr/bin/env python3
"""
Python PDF Export Utility for Reflections
------------------------------------------
Exports reflection documents into a clean, plain A4 PDF document containing
exclusively the reflection content:
- Document Title (16pt Bold, Centered)
- Metadata (Course, Instructor, Date if present, 11pt Centered)
- Reflection Body / Paragraphs (12pt Times-Roman, 0.5in indent, 1.5 line spacing, 1-inch margins)

Usage:
    python python/export_pdf.py [URL_or_slug_or_html] [output_pdf_path]
"""

import sys
import os
import re
import html
import urllib.request
import urllib.error

def build_plain_a4_pdf(title, metadata, paragraphs, output_path):
    """
    Standalone pure-Python PDF generator producing a plain A4 paper document.
    - Page Size: A4 (595.28 x 841.89 points)
    - Margins: 72 points (1 inch) on all sides
    - Fonts: Standard PDF Type1 Fonts (Times-Roman / Times-Bold / Times-Italic)
    """
    page_width = 595.28
    page_height = 841.89
    margin = 72.0  # 1 inch
    usable_width = page_width - (margin * 2)
    line_height = 18.0  # 1.5 spacing for 12pt font
    paragraph_gap = 12.0
    text_indent = 36.0  # 0.5 inch indent for paragraphs

    pages_content = []  # List of pages, each containing tuples: (font_key, font_size, x, y, text_string)
    current_page_ops = []
    
    # Start position at top margin
    y = page_height - margin - 20.0

    def new_page():
        nonlocal y, current_page_ops, pages_content
        if current_page_ops:
            pages_content.append(current_page_ops)
            current_page_ops = []
        y = page_height - margin - 20.0

    def check_space(needed=18.0):
        nonlocal y
        if y - needed < margin:
            new_page()

    # 1. Title - 16pt Times-Bold, Centered
    title_text = (title or "Reflection Paper").strip()
    title_width_approx = len(title_text) * 8.2
    title_x = max(margin, (page_width - title_width_approx) / 2)
    current_page_ops.append(('F2', 16, title_x, y, title_text))
    y -= 28.0

    # 2. Metadata (Student/Course information) - 11pt Times-Italic, Centered
    if metadata:
        meta_str = metadata if isinstance(metadata, str) else " · ".join(metadata)
        meta_str = meta_str.strip()
        if meta_str:
            meta_width_approx = len(meta_str) * 5.6
            meta_x = max(margin, (page_width - meta_width_approx) / 2)
            current_page_ops.append(('F3', 11, meta_x, y, meta_str))
            y -= 24.0

    y -= 10.0

    # 3. Reflection Body - 12pt Times-Roman, 1.5 line height, 0.5in indent
    for para in paragraphs:
        text = para.strip()
        if not text:
            continue

        words = text.split()
        if not words:
            continue

        check_space(line_height + paragraph_gap)
        
        current_line_words = []
        is_first_line = True

        for word in words:
            test_line = " ".join(current_line_words + [word])
            indent = text_indent if is_first_line else 0.0
            approx_len = indent + (len(test_line) * 6.1)

            if approx_len > usable_width and current_line_words:
                line_str = " ".join(current_line_words)
                line_x = margin + indent
                current_page_ops.append(('F1', 12, line_x, y, line_str))
                y -= line_height
                
                check_space(line_height)
                
                current_line_words = [word]
                is_first_line = False
            else:
                current_line_words.append(word)

        if current_line_words:
            line_str = " ".join(current_line_words)
            indent = text_indent if is_first_line else 0.0
            line_x = margin + indent
            current_page_ops.append(('F1', 12, line_x, y, line_str))
            y -= line_height

        y -= paragraph_gap

    if current_page_ops:
        pages_content.append(current_page_ops)

    num_pages = len(pages_content)
    if num_pages == 0:
        pages_content = [[('F1', 12, margin, page_height - margin - 20.0, "No content")]]
        num_pages = 1

    # Build PDF 1.4 Structure
    pdf_bytes = bytearray()
    pdf_bytes.extend(b"%PDF-1.4\n%\xe2\xe3\xcf\xd3\n")

    offsets = {}

    def add_obj(obj_num, content_bytes):
        offsets[obj_num] = len(pdf_bytes)
        pdf_bytes.extend(f"{obj_num} 0 obj\n".encode('ascii'))
        pdf_bytes.extend(content_bytes)
        pdf_bytes.extend(b"\nendobj\n")

    # Object 1: Catalog
    add_obj(1, b"<< /Type /Catalog /Pages 2 0 R >>")

    # Object 2: Pages Parent
    page_refs = " ".join([f"{3 + i*2} 0 R" for i in range(num_pages)])
    add_obj(2, f"<< /Type /Pages /Kids [{page_refs}] /Count {num_pages} >>".encode('ascii'))

    font_f1 = num_pages * 2 + 3
    font_f2 = font_f1 + 1
    font_f3 = font_f1 + 2

    # Objects for each page and its content stream
    for i, ops in enumerate(pages_content):
        page_obj_num = 3 + i * 2
        content_obj_num = page_obj_num + 1

        # Page Dictionary
        page_dict = (
            f"<< /Type /Page /Parent 2 0 R /MediaBox [0 0 {page_width} {page_height}] "
            f"/Resources << /Font << /F1 {font_f1} 0 R /F2 {font_f2} 0 R /F3 {font_f3} 0 R >> >> "
            f"/Contents {content_obj_num} 0 R >>"
        )
        add_obj(page_obj_num, page_dict.encode('ascii'))

        # Content Stream
        stream_ops = []
        for font_key, font_size, x_pos, y_pos, line_txt in ops:
            safe_txt = line_txt.replace('\\', '\\\\').replace('(', '\\(').replace(')', '\\)')
            stream_ops.append(
                f"BT /{font_key} {font_size} Tf 1 0 0 1 {x_pos:.2f} {y_pos:.2f} Tm ({safe_txt}) Tj ET"
            )

        stream_body = "\n".join(stream_ops).encode('latin-1', 'replace')
        stream_head = f"<< /Length {len(stream_body)} >>\nstream\n".encode('ascii')
        stream_tail = b"\nendstream"
        add_obj(content_obj_num, stream_head + stream_body + stream_tail)

    # Standard PDF Type1 Font Definitions
    add_obj(font_f1, b"<< /Type /Font /Subtype /Type1 /BaseFont /Times-Roman /Encoding /WinAnsiEncoding >>")
    add_obj(font_f2, b"<< /Type /Font /Subtype /Type1 /BaseFont /Times-Bold /Encoding /WinAnsiEncoding >>")
    add_obj(font_f3, b"<< /Type /Font /Subtype /Type1 /BaseFont /Times-Italic /Encoding /WinAnsiEncoding >>")

    # XRef Table and Trailer
    start_xref = len(pdf_bytes)
    total_objs = len(offsets)
    pdf_bytes.extend(f"xref\n0 {total_objs + 1}\n0000000000 65535 f \n".encode('ascii'))
    
    for obj_i in range(1, total_objs + 1):
        off = offsets[obj_i]
        pdf_bytes.extend(f"{off:010d} 00000 n \n".encode('ascii'))

    pdf_bytes.extend(
        f"trailer\n<< /Size {total_objs + 1} /Root 1 0 R >>\nstartxref\n{start_xref}\n%%EOF\n".encode('ascii')
    )

    out_dir = os.path.dirname(os.path.abspath(output_path))
    if out_dir:
        os.makedirs(out_dir, exist_ok=True)

    with open(output_path, 'wb') as f:
        f.write(pdf_bytes)

    return True

# Alias for backwards compatibility
build_word_style_pdf = build_plain_a4_pdf

def fetch_reflection_from_url(url):
    """Fetch HTML from URL and parse reflection title, meta, and paragraphs."""
    try:
        req = urllib.request.Request(url, headers={'User-Agent': 'PythonReflectionExporter/1.0'})
        with urllib.request.urlopen(req, timeout=10) as response:
            html_content = response.read().decode('utf-8')
            return parse_reflection_html(html_content)
    except Exception as err:
        print(f"Notice: Could not fetch from URL '{url}': {err}")
        return None

def parse_reflection_html(raw_html):
    """Extract reflection content from rendered HTML."""
    clean_html = html.unescape(raw_html)
    
    title_match = re.search(r'<(?:article|div)[^>]*class="[^"]*print-document[^"]*"[^>]*>.*?<h1>(.*?)</h1>', clean_html, re.DOTALL | re.IGNORECASE)
    if not title_match:
        title_match = re.search(r'<header[^>]*class="[^"]*reflection-document-header[^"]*"[^>]*>.*?<h1>(.*?)</h1>', clean_html, re.DOTALL | re.IGNORECASE)
    
    title = title_match.group(1).strip() if title_match else "Reflection Paper"
    title = re.sub(r'<[^>]+>', '', title)

    meta = []
    meta_match = re.search(r'<(?:p|div)[^>]*class="[^"]*reflection-meta[^"]*"[^>]*>(.*?)</(?:p|div)>', clean_html, re.DOTALL | re.IGNORECASE)
    if meta_match:
        meta_raw = re.sub(r'<[^>]+>', '', meta_match.group(1)).strip()
        meta = [m.strip() for m in meta_raw.split('·') if m.strip()]

    paragraphs = []
    print_doc_match = re.search(r'<article[^>]*class="[^"]*print-document[^"]*"[^>]*>(.*?)</article>', clean_html, re.DOTALL | re.IGNORECASE)
    body_html = print_doc_match.group(1) if print_doc_match else clean_html

    p_matches = re.findall(r'<p[^>]*>(.*?)</p>', body_html, re.DOTALL | re.IGNORECASE)
    for p_raw in p_matches:
        p_clean = re.sub(r'<[^>]+>', '', p_raw).strip()
        if p_clean and not any(meta_item in p_clean for meta_item in meta):
            paragraphs.append(p_clean)

    return title, meta, paragraphs

def fallback_sample_reflection():
    """Fallback sample reflection text if server is offline."""
    title = "Becoming a Professional IT Practitioner"
    meta = ["Bachelor of Information Technology", "JH Cerilles State College"]
    paragraphs = [
        "For me, professionalism in IT means being a responsible and knowledgeable person who has good skills in different IT fields. But I think having knowledge and being good at technology is not enough to call someone a professional. A professional should also have good manners and behavior. They should be someone you can trust and someone you can ask for help when you don't understand something. This is important, especially for people who are still new to the IT field. I believe that the way we treat and help other people is also part of being a professional.",
        "Among the qualities of an IT professional, I think integrity is my strongest quality. I prefer to be honest about the things I know and don't know instead of pretending that I know everything. If I don't know something, I would rather admit it and try to learn it. The quality that I still need to improve is competence because I know that there are still a lot of things in IT that I need to learn. When I find something that I don't know, I usually research about it, and it makes me more curious. I always want to learn and discover something new every day because there are still many things that I want to understand in IT.",
        "As an IT student, I believe that we are responsible for the information that we see and handle when creating or managing a system. We need to keep private information confidential and protect it so that no one will be harmed because of our actions. Just because we can access information does not mean that we can use or share it with other people. I also believe that we should be honest when there is a problem with the system we are working on. For example, if I find a security problem or mistake in our school project, I would tell my group about it and help them fix the problem instead of hiding or ignoring it.",
        "Cybersecurity is also part of our responsibility in IT. If I saw that my classmate or coworker wrote their password on a piece of paper beside their computer, I would tell them that it is not safe and that they should remove it and keep their password somewhere safer. I would never try to use their password because it is their private information. I also know that the things I do online can affect how other people see me. The things I post, comment, or say on social media can affect my reputation as an IT student and in the future as an IT professional. Because of this, I should be careful with what I do online and always respect other people.",
        "I know that I still need to continue learning even after I graduate because technology keeps changing. If I stop learning, there may be new technologies that I will not understand or know how to use. In the future, I want to learn more about web development and cybersecurity. I want to become better at web development because I want to create useful applications that everyone can use and that can provide help to people who need it, especially students. I also want to learn cybersecurity because I want to know how to protect the applications that I create and keep the information of the people using them safe.",
        "As a future IT professional, I commit myself to continue exploring and learning more about IT every day. I want to improve my skills and use what I learn to help other people. I will always keep the information given to me confidential and do my best to keep it safe. I will also treat people with respect and use my knowledge in a good way, not in a way that can harm other people. Most importantly, I want to use my knowledge and skills to serve people with all my heart and create something that can help make their lives easier. These are the things that I want to follow as I continue my studies and someday become an IT professional."
    ]
    return title, meta, paragraphs

def export_reflection_pdf(source="http://localhost:8000/?page=reflection", output_path="storage/reflection.pdf"):
    """Export reflection content to a clean, plain A4 PDF document."""
    data = None
    if source.startswith("http://") or source.startswith("https://"):
        data = fetch_reflection_from_url(source)

    if not data and os.path.exists(source) and not source.endswith(".pdf"):
        try:
            with open(source, "r", encoding="utf-8") as f:
                data = parse_reflection_html(f.read())
        except Exception as err:
            print(f"Could not read HTML source: {err}")

    if not data:
        data = fallback_sample_reflection()

    title, metadata, paragraphs = data

    build_plain_a4_pdf(
        title=title,
        metadata=metadata,
        paragraphs=paragraphs,
        output_path=output_path
    )

    print(f"Exported reflection PDF: {output_path}")
    return True

if __name__ == "__main__":
    args = sys.argv[1:]
    source_arg = "http://localhost:8000/?page=reflection"
    output_arg = "storage/reflection.pdf"

    if len(args) == 1:
        if args[0].endswith(".pdf"):
            output_arg = args[0]
        else:
            source_arg = args[0]
    elif len(args) >= 2:
        source_arg = args[0]
        output_arg = args[1]

    export_reflection_pdf(source_arg, output_arg)