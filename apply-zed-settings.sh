#!/bin/bash

# Script untuk apply Zed settings otomatis
# Usage: ./apply-zed-settings.sh

echo "🎨 Applying Zed Editor Settings..."
echo ""

# Lokasi settings Zed di macOS
ZED_SETTINGS="$HOME/Library/Application Support/Zed/settings.json"

# Backup settings lama jika ada
if [ -f "$ZED_SETTINGS" ]; then
    echo "📦 Backing up existing settings..."
    cp "$ZED_SETTINGS" "$ZED_SETTINGS.backup.$(date +%Y%m%d_%H%M%S)"
    echo "✅ Backup saved: $ZED_SETTINGS.backup.$(date +%Y%m%d_%H%M%S)"
    echo ""
fi

# Copy settings baru
echo "📋 Applying new settings..."
cp zed-settings.json "$ZED_SETTINGS"

if [ $? -eq 0 ]; then
    echo "✅ Settings applied successfully!"
    echo ""
    echo "📍 Settings location: $ZED_SETTINGS"
    echo ""
    echo "🔄 Please restart Zed Editor to apply changes"
    echo ""
    echo "🎯 Applied settings:"
    echo "   ✓ Font: Fira Code 13px with ligatures"
    echo "   ✓ Tab size: 4 spaces"
    echo "   ✓ Format on save: ON"
    echo "   ✓ Line width: 120"
    echo "   ✓ Theme: One Dark"
    echo ""
else
    echo "❌ Failed to apply settings"
    echo "Please copy manually from: $(pwd)/zed-settings.json"
    echo "To: $ZED_SETTINGS"
fi
