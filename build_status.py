from __future__ import annotations

import json
import sys
from datetime import datetime, timezone
from pathlib import Path


def safe_text(value: object, max_length: int = 180) -> str:
    text = str(value or "").strip()
    text = " ".join(text.split())
    return text[:max_length]


def main() -> int:
    if len(sys.argv) != 3:
        print("Usage: build_status.py INPUT OUTPUT", file=sys.stderr)
        return 2

    input_path = Path(sys.argv[1])
    output_path = Path(sys.argv[2])

    payload = json.loads(input_path.read_text(encoding="utf-8"))
    raw_rows = payload.get("data", [])

    if not payload.get("success") or not isinstance(raw_rows, list):
        raise RuntimeError("Invalid Xray Checker response")

    servers: list[dict[str, object]] = []

    for item in raw_rows:
        if not isinstance(item, dict):
            continue

        online = bool(item.get("online"))
        latency_raw = item.get("latencyMs")
        try:
            latency = max(0, int(latency_raw)) if online else None
        except (TypeError, ValueError):
            latency = None

        try:
            last_check = max(0, int(item.get("lastCheck") or 0))
        except (TypeError, ValueError):
            last_check = 0

        # فقط اطلاعات عمومی؛ آدرس، پورت و مشخصات اتصال منتشر نمی‌شوند.
        servers.append(
            {
                "id": safe_text(item.get("stableId"), 80),
                "name": safe_text(item.get("name") or "سرور بدون نام"),
                "group": safe_text(item.get("groupName"), 100),
                "online": online,
                "latencyMs": latency,
                "lastCheck": last_check,
            }
        )

    if not servers:
        raise RuntimeError("No proxy rows found")

    servers.sort(
        key=lambda row: (
            not bool(row["online"]),
            int(row["latencyMs"]) if row["latencyMs"] is not None else 10**9,
            str(row["name"]).casefold(),
        )
    )

    online_rows = [row for row in servers if row["online"]]
    valid_latencies = [
        int(row["latencyMs"])
        for row in online_rows
        if isinstance(row["latencyMs"], int)
    ]

    result = {
        "success": True,
        "updatedAt": datetime.now(timezone.utc).isoformat(),
        "total": len(servers),
        "online": len(online_rows),
        "offline": len(servers) - len(online_rows),
        "averageLatencyMs": (
            round(sum(valid_latencies) / len(valid_latencies))
            if valid_latencies
            else None
        ),
        "servers": servers,
    }

    output_path.parent.mkdir(parents=True, exist_ok=True)
    output_path.write_text(
        json.dumps(result, ensure_ascii=False, indent=2),
        encoding="utf-8",
    )
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
