EDITPRO STUDIO  (runs inside the Virtual Phone)
================================================
A phone-sized photo / video / music editor. It has NO phone frame of its own:
your Virtual Phone already provides that and opens this app in its overlay.

INSTALL
1. Copy this folder to your server as:  /virtual-phone/user/editpro/
2. In your phone's index file, add this inside <div class="apps-grid"> (before the Logout link):

<a href="/virtual-phone/user/editpro/index.html" class="app">
  <div class="icon" style="background:linear-gradient(135deg,#ff5a7a,#ffc857)"><i class="fas fa-clapperboard"></i></div>
  <span>EditPro</span>
</a>

Your launcher script already opens every .app link in the phone overlay (adds ?embedded=1),
and the iframe already allows the microphone, so recording works. No PHP, Node or database needed.
To test alone, just open editpro/index.html in a browser (use https or localhost for the microphone).

WHAT WORKS
Photo : open, filters, brightness/contrast/saturation/blur, crop ratios, rotate, flip, frames,
        text, emoji stickers (tap photo to move), resize 1080/720/480, save PNG.
Video : open, play, timeline, trim start/end, rotate, speed, volume, filters, adjustments,
        caption, export (records in real time through Canvas + MediaRecorder).
Music : open, waveform, trim, fade in/out, volume, record mic, append clips, built-in
        beep/chime/whoosh effects, preview, save WAV.

LIMITATIONS (honest list)
- Video export plays the clip in real time and records it. Keep this screen open until it finishes.
- iPhone Safari cannot capture audio from a <video> element, so exported video may be silent there,
  and older Safari ignores canvas filters (video filters may not appear in the export).
- Export is MP4 where the browser supports it, otherwise WebM. Max export size is 720p.
- Not included: split/delete middle sections, multitrack, transitions, undo/redo history,
  project saving, templates, background removal, and AI tools (these need an AI API or FFmpeg.wasm).
