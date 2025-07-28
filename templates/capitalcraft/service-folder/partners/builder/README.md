# Corporate Partner Section

A responsive corporate-style partner section featuring auto-scrolling bank logos with smooth animations.

## Features

- **Responsive Design**: Shows 3 logos on desktop, 2 on tablet, 1 on mobile
- **Auto-scroll**: Automatically scrolls every 5 seconds with smooth animation
- **Infinite Loop**: Seamless infinite scrolling without jarring resets
- **Hover Pause**: Auto-scroll pauses when hovering over the logos
- **Corporate Styling**: Clean, professional design with thin borders and proper typography

## Setup

1. Place your logo images in the root directory with the following names:
   - `logo1.png` - First bank logo
   - `logo2.png` - Second bank logo
   - `logo3.png` - Third bank logo
   - `logo4.png` - Fourth bank logo
   - `logo5.png` - Fifth bank logo
   - `logo6.png` - Sixth bank logo

2. Open `index.html` in a web browser to view the component

## Logo Requirements

- **Format**: PNG, JPG, or SVG
- **Dimensions**: Recommended max height of 55px
- **Background**: Transparent or white background works best
- **Aspect Ratio**: Logos will maintain their aspect ratio and be contained within the allocated space

## Customization

### Text Content
Edit the text in the `title-text` div in `index.html`:
```html
<div class="title-text">
    НАМ ДОВЕРЯЮТ<br>КРУПНЕЙШИЕ БАНКИ<br>И ЛИЗИНГОВЫЕ КОМПАНИИ
</div>
```

### Auto-scroll Timing
Modify the interval in `script.js` (currently set to 5000ms = 5 seconds):
```javascript
setInterval(() => {
    this.nextSlide();
}, 5000); // Change this value
```

### Responsive Breakpoints
Adjust breakpoints in `styles.css`:
- Desktop: `min-width: 1024px`
- Tablet: `min-width: 768px` and `max-width: 1023px`
- Mobile: `max-width: 767px`

### Colors and Styling
Main styling variables in `styles.css`:
- Border color: `#000000`
- Text color: `#000000`
- Background: `#ffffff`
- Font family: `'Golos Text'`

## Browser Compatibility

- Modern browsers (Chrome, Firefox, Safari, Edge)
- CSS Grid and Flexbox support required
- JavaScript ES6+ support required

## File Structure

```
├── index.html          # Main HTML file
├── styles.css          # CSS styling
├── script.js           # JavaScript functionality
├── logo1.png          # Bank logo 1 (you need to add)
├── logo2.png          # Bank logo 2 (you need to add)
├── logo3.png          # Bank logo 3 (you need to add)
├── logo4.png          # Bank logo 4 (you need to add)
├── logo5.png          # Bank logo 5 (you need to add)
├── logo6.png          # Bank logo 6 (you need to add)
└── README.md          # This file
```
