 // Define color schemes
 const colorSchemes = {
  default: {
      theme: 'light',
      bgColor: '#fafafa',
      textColor: '#333',
      headerColor: '#667eea',
      primaryColor: '#667eea',
      navBgColor: '#f6f7f9',
      navTextColor: '#333',
      navHoverBgColor: '#667eea',
      navHoverTextColor: 'white',
      navAccentColor: '#667eea'
  },
  dark: {
      theme: 'dark',
      bgColor: '#1a1a1a',
      textColor: '#e0e0e0',
      headerColor: '#4a5568',
      primaryColor: '#667eea',
      navBgColor: '#2d3748',
      navTextColor: '#e0e0e0',
      navHoverBgColor: '#4a5568',
      navHoverTextColor: 'white',
      navAccentColor: '#667eea'
  },
  'dark-blue': {
      theme: 'dark',
      bgColor: '#0f172a',
      textColor: '#cbd5e1',
      headerColor: '#1e40af',
      primaryColor: '#3b82f6',
      navBgColor: '#1e293b',
      navTextColor: '#cbd5e1',
      navHoverBgColor: '#1e40af',
      navHoverTextColor: 'white',
      navAccentColor: '#60a5fa'
  },
  'dark-gray': {
      theme: 'dark',
      bgColor: '#111827',
      textColor: '#d1d5db',
      headerColor: '#4b5563',
      primaryColor: '#6b7280',
      navBgColor: '#1f2937',
      navTextColor: '#d1d5db',
      navHoverBgColor: '#4b5563',
      navHoverTextColor: 'white',
      navAccentColor: '#9ca3af'
  },
  'dark-teal': {
      theme: 'dark',
      bgColor: '#0f1419',
      textColor: '#a7f3d0',
      headerColor: '#0f766e',
      primaryColor: '#14b8a6',
      navBgColor: '#1b2e2a',
      navTextColor: '#a7f3d0',
      navHoverBgColor: '#0f766e',
      navHoverTextColor: 'white',
      navAccentColor: '#5eead4'
  },
  green: {
      theme: 'light',
      bgColor: '#f0fdf4',
      textColor: '#064e3b',
      headerColor: '#059669',
      primaryColor: '#10b981',
      navBgColor: '#ecfdf5',
      navTextColor: '#064e3b',
      navHoverBgColor: '#059669',
      navHoverTextColor: 'white',
      navAccentColor: '#10b981'
  },
  purple: {
      theme: 'light',
      bgColor: '#faf5ff',
      textColor: '#581c87',
      headerColor: '#7c3aed',
      primaryColor: '#8b5cf6',
      navBgColor: '#f3e8ff',
      navTextColor: '#581c87',
      navHoverBgColor: '#7c3aed',
      navHoverTextColor: 'white',
      navAccentColor: '#8b5cf6'
  },
  orange: {
      theme: 'light',
      bgColor: '#fff7ed',
      textColor: '#9a3412',
      headerColor: '#ea580c',
      primaryColor: '#f97316',
      navBgColor: '#fed7aa',
      navTextColor: '#9a3412',
      navHoverBgColor: '#ea580c',
      navHoverTextColor: 'white',
      navAccentColor: '#f97316'
  }
};

function changeColorScheme() {
  const selector = document.getElementById('color-scheme-select');
  const rapidocElement = document.getElementById('rapidoc-element');

  if (selector && rapidocElement) {
      const scheme = colorSchemes[selector.value];

      if (scheme) {
          rapidocElement.setAttribute('theme', scheme.theme);
          rapidocElement.setAttribute('bg-color', scheme.bgColor);
          rapidocElement.setAttribute('text-color', scheme.textColor);
          rapidocElement.setAttribute('header-color', scheme.headerColor);
          rapidocElement.setAttribute('primary-color', scheme.primaryColor);
          rapidocElement.setAttribute('nav-bg-color', scheme.navBgColor);
          rapidocElement.setAttribute('nav-text-color', scheme.navTextColor);
          rapidocElement.setAttribute('nav-hover-bg-color', scheme.navHoverBgColor);
          rapidocElement.setAttribute('nav-hover-text-color', scheme.navHoverTextColor);
          rapidocElement.setAttribute('nav-accent-color', scheme.navAccentColor);

          // Store user preference in localStorage.
          localStorage.setItem('rapidoc-color-scheme', selector.value);
      }
  }
}

function changeRenderStyle() {
  const selector = document.getElementById('render-style-select');
  const rapidocElement = document.getElementById('rapidoc-element');

  if (selector && rapidocElement) {
      rapidocElement.setAttribute('render-style', selector.value);

      // Store user preference in localStorage.
      localStorage.setItem('rapidoc-render-style', selector.value);
  }
}

function changeFontSize() {
  const selector = document.getElementById('font-size-select');
  const rapidocElement = document.getElementById('rapidoc-element');

  if (selector && rapidocElement) {
      // Remove existing font size classes.
      rapidocElement.classList.remove('rapidoc-font-small', 'rapidoc-font-default', 'rapidoc-font-large', 'rapidoc-font-x-large');

      // Add new font size class.
      rapidocElement.classList.add(`rapidoc-font-${selector.value}`);

      // Store user preference in localStorage.
      localStorage.setItem('rapidoc-font-size', selector.value);
  }
}

// Load user preferences on page load.
document.addEventListener('DOMContentLoaded', function() {
  const savedStyle = localStorage.getItem('rapidoc-render-style');
  const savedColorScheme = localStorage.getItem('rapidoc-color-scheme');
  const savedFontSize = localStorage.getItem('rapidoc-font-size');
  const styleSelector = document.getElementById('render-style-select');
  const colorSelector = document.getElementById('color-scheme-select');
  const fontSizeSelector = document.getElementById('font-size-select');
  const rapidocElement = document.getElementById('rapidoc-element');

  // Load render style preference.
  if (savedStyle && styleSelector && rapidocElement) {
      styleSelector.value = savedStyle;
      rapidocElement.setAttribute('render-style', savedStyle);
  }

  // Load color scheme preference.
  if (savedColorScheme && colorSelector && rapidocElement) {
      colorSelector.value = savedColorScheme;
      const scheme = colorSchemes[savedColorScheme];

      if (scheme) {
          rapidocElement.setAttribute('theme', scheme.theme);
          rapidocElement.setAttribute('bg-color', scheme.bgColor);
          rapidocElement.setAttribute('text-color', scheme.textColor);
          rapidocElement.setAttribute('header-color', scheme.headerColor);
          rapidocElement.setAttribute('primary-color', scheme.primaryColor);
          rapidocElement.setAttribute('nav-bg-color', scheme.navBgColor);
          rapidocElement.setAttribute('nav-text-color', scheme.navTextColor);
          rapidocElement.setAttribute('nav-hover-bg-color', scheme.navHoverBgColor);
          rapidocElement.setAttribute('nav-hover-text-color', scheme.navHoverTextColor);
          rapidocElement.setAttribute('nav-accent-color', scheme.navAccentColor);
      }
  }

  // Load font size preference.
  if (savedFontSize && fontSizeSelector && rapidocElement) {
      fontSizeSelector.value = savedFontSize;
      rapidocElement.classList.remove('rapidoc-font-small', 'rapidoc-font-default', 'rapidoc-font-large', 'rapidoc-font-x-large');
      rapidocElement.classList.add(`rapidoc-font-${savedFontSize}`);
  } else if (rapidocElement) {
      // Apply default font size if no preference is saved.
      rapidocElement.classList.add('rapidoc-font-default');
  }
});
