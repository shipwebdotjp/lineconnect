#!/usr/bin/env python3
"""Translate untranslated PO entries with OpenAI and validate with msgfmt.

This script reads a .po file, translates only entries that are still empty,
writes the updated file to a temporary path, validates it with `msgfmt -c`,
and replaces the original file only after validation succeeds.

Dependencies:
  - polib
  - msgfmt from GNU gettext

Environment:
  - OPENAI_API_KEY

Example:
  python3 tools/translate_po.py languages/lineconnect-ja.po
"""

from __future__ import annotations

import argparse
import json
import os
import re
import shutil
import subprocess
import sys
import tempfile
import textwrap
from datetime import datetime
from pathlib import Path
from typing import Any, Dict, List, Optional
from urllib.error import HTTPError, URLError
from urllib.request import Request, urlopen

try:
    import polib
except ImportError as exc:  # pragma: no cover - import guard
    raise SystemExit(
        "polib is required. Install it with: python3 -m pip install polib"
    ) from exc


DEFAULT_MODEL = os.environ.get("OPENAI_MODEL", "gpt-5.4-mini")
DEFAULT_BASE_URL = os.environ.get("OPENAI_BASE_URL", "https://api.openai.com/v1")
DEFAULT_TARGET_LANG = "ja"
DEFAULT_SOURCE_LANG = "en"
DEFAULT_RETRIES = 3


class TranslationError(RuntimeError):
    """Raised when translation or validation fails."""


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Translate untranslated strings in a PO file using OpenAI."
    )
    parser.add_argument("po_file", type=Path, help="Path to the .po file to update")
    parser.add_argument(
        "--target-lang",
        default=DEFAULT_TARGET_LANG,
        help="Target language code (default: ja)",
    )
    parser.add_argument(
        "--source-lang",
        default=DEFAULT_SOURCE_LANG,
        help="Source language code used in prompts (default: en)",
    )
    parser.add_argument(
        "--model",
        default=DEFAULT_MODEL,
        help="OpenAI model name (default: from OPENAI_MODEL or gpt-5.4-mini)",
    )
    parser.add_argument(
        "--base-url",
        default=DEFAULT_BASE_URL,
        help="OpenAI-compatible base URL (default: from OPENAI_BASE_URL or OpenAI)",
    )
    parser.add_argument(
        "--api-key",
        default=os.environ.get("OPENAI_API_KEY"),
        help="OpenAI API key (default: OPENAI_API_KEY)",
    )
    parser.add_argument(
        "--dry-run",
        action="store_true",
        help="Translate in memory only and do not write any files",
    )
    parser.add_argument(
        "--retries",
        type=int,
        default=DEFAULT_RETRIES,
        help="Retry count per entry on API or parse failure",
    )
    return parser.parse_args()


def load_po(po_path: Path) -> "polib.POFile":
    if not po_path.exists():
        raise TranslationError(f"PO file not found: {po_path}")
    return polib.pofile(str(po_path), encoding="utf-8")


def extract_nplurals(po: "polib.POFile") -> int:
    plural_forms = po.metadata.get("Plural-Forms", "")
    match = re.search(r"nplurals\s*=\s*(\d+)", plural_forms)
    if match:
        return int(match.group(1))
    return 1


def is_untranslated(entry: "polib.POEntry", nplurals: int) -> bool:
    if entry.obsolete or entry.fuzzy:
        return False

    if entry.msgid_plural:
        if not entry.msgstr_plural:
            return True
        return all(
            not (entry.msgstr_plural.get(index) or "").strip()
            for index in range(nplurals)
        )

    return not (entry.msgstr or "").strip()


def collect_untranslated_entries(
    po: "polib.POFile", nplurals: int
) -> List["polib.POEntry"]:
    return [entry for entry in po if is_untranslated(entry, nplurals)]


def build_payload(
    entry: "polib.POEntry",
    source_lang: str,
    target_lang: str,
    nplurals: int,
) -> Dict[str, Any]:
    context = {
        "source_language": source_lang,
        "target_language": target_lang,
        "msgid": entry.msgid,
    }
    if entry.msgctxt:
        context["msgctxt"] = entry.msgctxt
    if entry.msgid_plural:
        context["msgid_plural"] = entry.msgid_plural
        context["plural_forms"] = nplurals
    return context


def openai_chat_completion(
    api_key: str,
    base_url: str,
    model: str,
    messages: List[Dict[str, str]],
    timeout: int = 120,
) -> str:
    base_url = base_url.rstrip("/")
    url = f"{base_url}/chat/completions"
    body = {
        "model": model,
        "messages": messages,
        "temperature": 0,
        "response_format": {"type": "json_object"},
    }
    data = json.dumps(body).encode("utf-8")
    request = Request(
        url,
        data=data,
        headers={
            "Authorization": f"Bearer {api_key}",
            "Content-Type": "application/json",
        },
        method="POST",
    )

    try:
        with urlopen(request, timeout=timeout) as response:
            raw = response.read().decode("utf-8")
    except HTTPError as exc:
        detail = exc.read().decode("utf-8", errors="replace") if exc.fp else str(exc)
        raise TranslationError(f"OpenAI API request failed: {detail}") from exc
    except URLError as exc:
        raise TranslationError(f"OpenAI API connection failed: {exc}") from exc

    payload = json.loads(raw)
    choices = payload.get("choices") or []
    if not choices:
        raise TranslationError(f"OpenAI API returned no choices: {raw}")
    message = choices[0].get("message") or {}
    content = message.get("content")
    if not content:
        raise TranslationError(f"OpenAI API returned empty content: {raw}")
    return content


def translation_prompt(
    entry: "polib.POEntry",
    source_lang: str,
    target_lang: str,
    nplurals: int,
) -> List[Dict[str, str]]:
    system = textwrap.dedent(
        f"""
        You are a professional localization engine.
        Translate from {source_lang} to {target_lang}.
        Preserve placeholders, printf-style tokens, HTML tags, code fragments,
        newline structure, and punctuation that must remain intact.
        Return only valid JSON and nothing else.
        """
    ).strip()

    if entry.msgid_plural:
        user_obj = build_payload(entry, source_lang, target_lang, nplurals)
        user = textwrap.dedent(
            """
            Translate this PO entry.
            Return JSON in this exact shape:
            {"translations":["..."]}
            The array length must match the target language plural forms.
            """
        ).strip()
        user += "\n" + json.dumps(user_obj, ensure_ascii=False, indent=2)
    else:
        user_obj = build_payload(entry, source_lang, target_lang, nplurals)
        user = textwrap.dedent(
            """
            Translate this PO entry.
            Return JSON in this exact shape:
            {"translation":"..."}
            """
        ).strip()
        user += "\n" + json.dumps(user_obj, ensure_ascii=False, indent=2)

    return [
        {"role": "system", "content": system},
        {"role": "user", "content": user},
    ]


def parse_translation_response(
    content: str,
    entry: "polib.POEntry",
    nplurals: int,
) -> List[str]:
    parsed = json.loads(content)
    if entry.msgid_plural:
        translations = parsed.get("translations")
        if not isinstance(translations, list) or not translations:
            raise TranslationError(f"Invalid plural translation payload: {content}")
        result = [str(value) for value in translations]
        if len(result) != nplurals:
            raise TranslationError(
                f"Plural translation count mismatch: expected {nplurals}, got {len(result)}"
            )
        return result

    translation = parsed.get("translation")
    if not isinstance(translation, str):
        raise TranslationError(f"Invalid translation payload: {content}")
    return [translation]


def format_translation_log(
    entry: "polib.POEntry",
    translations: List[str],
) -> str:
    if entry.msgid_plural:
        lines = [f"  msgid_plural: {entry.msgid_plural}"]
        for index, translation in enumerate(translations):
            lines.append(f"  [{index}] {translation}")
        return "\n".join(lines)

    return f"  {entry.msgid} -> {translations[0]}"


def translate_entry(
    entry: "polib.POEntry",
    api_key: str,
    base_url: str,
    model: str,
    source_lang: str,
    target_lang: str,
    nplurals: int,
    retries: int,
) -> None:
    messages = translation_prompt(entry, source_lang, target_lang, nplurals)
    last_error: Optional[Exception] = None

    for attempt in range(1, retries + 1):
        try:
            content = openai_chat_completion(api_key, base_url, model, messages)
            translations = parse_translation_response(content, entry, nplurals)
            if entry.msgid_plural:
                entry.msgstr_plural = {
                    index: translations[index] for index in range(len(translations))
                }
            else:
                entry.msgstr = translations[0]
            print(
                "Translated:\n" + format_translation_log(entry, translations),
                file=sys.stderr,
            )
            return
        except Exception as exc:  # noqa: BLE001
            last_error = exc
            if attempt == retries:
                break
            print(
                f"Retrying entry {entry.msgid!r} ({attempt}/{retries}) after error: {exc}",
                file=sys.stderr,
            )

    raise TranslationError(
        f"Failed to translate entry {entry.msgid!r}: {last_error}"
    ) from last_error


def validate_po(po_path: Path) -> None:
    msgfmt = shutil.which("msgfmt")
    if not msgfmt:
        raise TranslationError("msgfmt not found in PATH")

    with tempfile.NamedTemporaryFile(
        suffix=".mo", delete=False, dir=str(po_path.parent)
    ) as tmp_mo:
        tmp_mo_path = Path(tmp_mo.name)

    try:
        subprocess.run(
            [msgfmt, "-c", "-o", str(tmp_mo_path), str(po_path)],
            check=True,
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            text=True,
        )
    except subprocess.CalledProcessError as exc:
        raise TranslationError(
            "msgfmt validation failed:\n" + (exc.stderr or exc.stdout or "")
        ) from exc
    finally:
        if tmp_mo_path.exists():
            tmp_mo_path.unlink(missing_ok=True)


def write_po_safely(po: "polib.POFile", po_path: Path) -> None:
    with tempfile.NamedTemporaryFile(
        suffix=".po", delete=False, dir=str(po_path.parent), mode="w", encoding="utf-8"
    ) as tmp_file:
        tmp_path = Path(tmp_file.name)
        po.save(str(tmp_path))
    shutil.copymode(po_path, tmp_path)

    try:
        validate_po(tmp_path)
        backup_path = po_path.with_suffix(po_path.suffix + ".bak")
        shutil.copy2(po_path, backup_path)
        tmp_path.replace(po_path)
    except Exception:
        tmp_path.unlink(missing_ok=True)
        raise


def update_revision_date(po: "polib.POFile") -> None:
    po.metadata["PO-Revision-Date"] = datetime.now().astimezone().strftime(
        "%Y-%m-%d %H:%M%z"
    )


def main() -> int:
    args = parse_args()
    if not args.api_key:
        raise SystemExit("OPENAI_API_KEY is required")

    po = load_po(args.po_file)
    nplurals = extract_nplurals(po)
    untranslated = collect_untranslated_entries(po, nplurals)

    print(f"Untranslated entries: {len(untranslated)}", file=sys.stderr)
    if not untranslated:
        print("Nothing to translate.", file=sys.stderr)
        return 0

    for index, entry in enumerate(untranslated, start=1):
        print(f"[{index}/{len(untranslated)}] Translating: {entry.msgid!r}", file=sys.stderr)
        translate_entry(
            entry=entry,
            api_key=args.api_key,
            base_url=args.base_url,
            model=args.model,
            source_lang=args.source_lang,
            target_lang=args.target_lang,
            nplurals=nplurals,
            retries=args.retries,
        )

    update_revision_date(po)

    if args.dry_run:
        print("Dry run complete. No files were written.", file=sys.stderr)
        return 0

    write_po_safely(po, args.po_file)
    print(f"Updated: {args.po_file}", file=sys.stderr)
    print(f"Backup:   {args.po_file.with_suffix(args.po_file.suffix + '.bak')}", file=sys.stderr)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
