# Dark Mode Implementation for User Dashboard

I have successfully implemented a comprehensive Dark Mode for your User Dashboard (`user.php`).

## 1. Features Implemented

### 🎨 Visual Design
- **Color Palette**: 
  - Uses a deep blue/slate theme (`#0f172a`, `#1e293b`) for a modern, professional look instead of pure black.
  - Text colors automatically adjust to high-contrast white/grey for readability.
- **Components Styled**:
  - **Cards & Stats**: Fully adapted backgrounds and borders.
  - **Tables**: Rows and headers switch to dark themes with hover effects.
  - **Modals**: Popups for Shipment Details, Waybills, and Ratings match the theme.
  - **Dropdowns**: Notification and Profile menus are fully styled.
  - **Forms**: Input fields and search bars have dark backgrounds with focus rings.

### 🗺️ Map Integration
- **Advanced Filtering**: The Leaflet map now uses a CSS filter in Dark Mode to invert and hue-rotate tiles. This creates a "dark map" effect without needing a custom map provider API key!
  - *Tech Detail*: `filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%);`

### ⚡ Interaction
- **Toggle Switch**: Added a moon icon toggle in the header.
- **Persistence**: Your preference is saved in local storage (handled by `darkmode.php`), so it remembers your choice when you refresh or return.
- **Smooth Transitions**: Colors transition smoothly when toggling.

## 2. Updated Files
- `user.php`: Added CSS variables, overrides, and toggle switch.
- `darkmode.php`: Verified logic for persisting state.

## 3. How to Use
1.  **Locate the Toggle**: Look for the toggle switch with a 🌙 icon in the top header, next to the notification bell.
2.  **Click it**: The entire interface will instantly switch to Dark Mode.
3.  **Map Check**: Open a "Track Shipment" modal while in Dark Mode to see the dark map effect.

Your dashboard is now easier on the eyes and looks much more premium! 🚀
