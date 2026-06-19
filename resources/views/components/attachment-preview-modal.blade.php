<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('attachmentPreview', {
        open: false,
        url: '',
        downloadUrl: '',
        name: '',
        type: 'other', // image | pdf | other

        show(url, downloadUrl, name, type) {
            this.url = url;
            this.downloadUrl = downloadUrl || url;
            this.name = name || '';
            this.type = type || 'other';
            this.open = true;
        },

        close() {
            this.open = false;
            this.url = '';
        },
    });
});
</script>

<div x-data="{
        w: 880, h: 600, x: 0, y: 0,
        drag: null, resize: null,

        point(e) {
            return e.touches ? { x: e.touches[0].clientX, y: e.touches[0].clientY } : { x: e.clientX, y: e.clientY };
        },

        resetGeometry() {
            const w = Math.min(880, window.innerWidth - 40);
            const h = Math.min(640, window.innerHeight - 40);
            this.w = w;
            this.h = h;
            this.x = (window.innerWidth - w) / 2;
            this.y = (window.innerHeight - h) / 2;
        },

        startDrag(e) {
            const p = this.point(e);
            this.drag = { startX: p.x, startY: p.y, origX: this.x, origY: this.y };
        },
        onDrag(e) {
            if (!this.drag) return;
            const p = this.point(e);
            this.x = this.drag.origX + (p.x - this.drag.startX);
            this.y = this.drag.origY + (p.y - this.drag.startY);
        },
        stopDrag() { this.drag = null; },

        startResize(e) {
            const p = this.point(e);
            this.resize = { startX: p.x, startY: p.y, origW: this.w, origH: this.h };
        },
        onResize(e) {
            if (!this.resize) return;
            const p = this.point(e);
            this.w = Math.min(window.innerWidth - this.x - 10, Math.max(360, this.resize.origW + (p.x - this.resize.startX)));
            this.h = Math.min(window.innerHeight - this.y - 10, Math.max(280, this.resize.origH + (p.y - this.resize.startY)));
        },
        stopResize() { this.resize = null; },

        pdfInlineSupported() {
            return typeof navigator.pdfViewerEnabled !== 'undefined' ? navigator.pdfViewerEnabled : window.innerWidth > 768;
        },
     }"
     x-show="$store.attachmentPreview.open"
     x-cloak
     x-init="$watch(() => $store.attachmentPreview.open, (isOpen) => { if (isOpen) resetGeometry(); })"
     @mousemove.window="onDrag($event); onResize($event)" @touchmove.window="onDrag($event); onResize($event)"
     @mouseup.window="stopDrag(); stopResize()" @touchend.window="stopDrag(); stopResize()"
     style="position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:900"
     @click.self="$store.attachmentPreview.close()"
     @keydown.escape.window="$store.attachmentPreview.close()">

    <div :style="{
            position: 'fixed', left: x+'px', top: y+'px', width: w+'px', height: h+'px',
            background: '#fff', borderRadius: '14px', display: 'flex', flexDirection: 'column',
            boxShadow: '0 20px 60px rgba(0,0,0,.25)', overflow: 'hidden',
         }">

        {{-- Header (drag handle) --}}
        <div @mousedown="startDrag($event)" @touchstart="startDrag($event)"
             style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 18px;border-bottom:1px solid var(--warm-bd);cursor:move;touch-action:none;user-select:none;flex-shrink:0">
            <div style="font-size:.875rem;font-weight:600;color:var(--txt);white-space:nowrap;overflow:hidden;text-overflow:ellipsis" x-text="$store.attachmentPreview.name"></div>
            <div style="display:flex;align-items:center;gap:8px;flex-shrink:0" @mousedown.stop @touchstart.stop>
                <a :href="$store.attachmentPreview.downloadUrl" :download="$store.attachmentPreview.name"
                   style="display:flex;align-items:center;gap:5px;font-size:.8rem;color:var(--txt-md);text-decoration:none;border:1.5px solid var(--warm-bd);border-radius:8px;padding:6px 12px;background:var(--parch)"
                   onmouseover="this.style.background='var(--warm-bd)'" onmouseout="this.style.background='var(--parch)'">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
                    Download
                </a>
                <button type="button" @click="$store.attachmentPreview.close()"
                        style="display:flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:8px;border:none;background:transparent;color:var(--txt-lt);cursor:pointer">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Body --}}
        <div style="flex:1;overflow:auto;background:var(--parch);display:flex;align-items:center;justify-content:center;min-height:0">
            <template x-if="$store.attachmentPreview.type === 'image'">
                <img :src="$store.attachmentPreview.url" :alt="$store.attachmentPreview.name"
                     style="max-width:100%;max-height:100%;object-fit:contain">
            </template>
            <template x-if="$store.attachmentPreview.type === 'pdf' && pdfInlineSupported()">
                <iframe :src="$store.attachmentPreview.url" style="width:100%;height:100%;border:none"></iframe>
            </template>
            <template x-if="$store.attachmentPreview.type === 'pdf' && !pdfInlineSupported()">
                <div style="text-align:center;padding:40px;color:var(--txt-lt)">
                    <div style="font-size:2.5rem;margin-bottom:10px">📄</div>
                    <div style="font-size:.875rem;margin-bottom:14px;word-break:break-word" x-text="$store.attachmentPreview.name"></div>
                    <a :href="$store.attachmentPreview.url" target="_blank" rel="noopener"
                       style="display:inline-flex;align-items:center;gap:6px;font-size:.85rem;font-weight:600;color:#fff;background:var(--plum,#4a3760);border-radius:9px;padding:9px 18px;text-decoration:none">
                        Open PDF
                    </a>
                </div>
            </template>
            <template x-if="$store.attachmentPreview.type === 'other'">
                <div style="text-align:center;padding:40px;color:var(--txt-lt)">
                    <div style="font-size:.875rem;margin-bottom:14px">Preview isn't available for this file type.</div>
                    <a :href="$store.attachmentPreview.downloadUrl" :download="$store.attachmentPreview.name"
                       style="display:inline-flex;align-items:center;gap:6px;font-size:.85rem;font-weight:600;color:#fff;background:var(--leaf);border-radius:9px;padding:9px 18px;text-decoration:none">
                        Download instead
                    </a>
                </div>
            </template>
        </div>

        {{-- Resize handle --}}
        <div @mousedown.stop="startResize($event)" @touchstart.stop="startResize($event)"
             style="position:absolute;right:0;bottom:0;width:18px;height:18px;cursor:nwse-resize;touch-action:none;background:linear-gradient(135deg, transparent 50%, var(--warm-bd) 50%)"></div>
    </div>
</div>
