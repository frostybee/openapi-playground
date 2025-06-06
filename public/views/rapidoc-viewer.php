<?php
// RapiDoc Template
// This template is included by viewer.php when renderer is 'rapidoc'
?>

<script type="module" src="https://unpkg.com/rapidoc/dist/rapidoc-min.js"></script>
<link rel="stylesheet" href="./assets/css/rapidoc-viewer.css">

<div class="rapidoc-toolbar">
    <div class="toolbar-left">
        <!-- Space for future control buttons -->
    </div>

    <div class="toolbar-right">
        <div class="render-style-selector">
            <label for="color-scheme-select">Color Scheme:</label>
            <select id="color-scheme-select" onchange="changeColorScheme()">
                <option value="default" selected>Default</option>
                <option value="dark">Dark</option>
                <option value="dark-blue">Dark Blue</option>
                <option value="dark-gray">Dark Gray</option>
                <option value="dark-teal">Dark Teal</option>
                <option value="green">Green</option>
                <option value="purple">Purple</option>
                <option value="orange">Orange</option>
            </select>
        </div>

        <div class="toolbar-divider"></div>

        <div class="render-style-selector">
            <label for="font-size-select">Font Size:</label>
            <select id="font-size-select" onchange="changeFontSize()">
                <option value="small">Small</option>
                <option value="default" selected>Default</option>
                <option value="large">Large</option>
                <option value="x-large">Extra Large</option>
            </select>
        </div>

        <div class="toolbar-divider"></div>

        <div class="render-style-selector">
            <label for="render-style-select">View Mode:</label>
            <select id="render-style-select" onchange="changeRenderStyle()">
                <option value="view">View</option>
                <option value="read" selected>Read</option>
                <option value="focused">Focused</option>
            </select>
        </div>
    </div>
</div>

<rapi-doc
    id="rapidoc-element"
    spec-url="viewer.php?id=<?php echo $fileId; ?>&spec=1"
    theme="light"
    bg-color="#fafafa"
    text-color="#333"
    header-color="#667eea"
    primary-color="#667eea"
    render-style="read"
    nav-bg-color="#f6f7f9"
    nav-text-color="#333"
    nav-hover-bg-color="#667eea"
    nav-hover-text-color="white"
    nav-accent-color="#667eea"
    show-header="false"
    allow-try="true"
    allow-authentication="true"
    allow-server-selection="true"
    default-schema-tab="schema"
    schema-style="tree"
    schema-expand-level="1">
</rapi-doc>

<script src="./assets/js/rapidoc-viewer.js"></script>
