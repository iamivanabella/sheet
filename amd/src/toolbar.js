define([], function() {
    let boldButton;
    let italicButton;
    let underlineButton;
    let textColorPicker;
    let fillColorPicker;
    let fontSizeDropdown;
    let alignDropdownButton;
    let alignLeftButton;
    let alignCenterButton;
    let alignRightButton;
    let dropdownContent;

    function updateAlignmentIcon(alignment) {
        const iconMappings = {
            "htLeft": alignLeftIcon,
            "htCenter": alignCenterIcon,
            "htRight": alignRightIcon
        };
        const iconUrl = iconMappings[alignment] || alignLeftIcon;

        if (alignDropdownButton) {
            alignDropdownButton.innerHTML = `<img src="${iconUrl}" alt="Align" class="toolbar-icon">`;
        }
    }

    function hideAlignmentDropdown() {
        if (dropdownContent) {
            dropdownContent.style.display = "none";
        }
    }

    function applyToSelectedCells(getSelectedCells, hot, callback) {
        const selectedCells = getSelectedCells();
        if (selectedCells.length > 0) {
            selectedCells.forEach(callback);
            hot.render(); // Re-render the table to apply changes
        }
    }

    function initToolbar(hot, getSelectedCells) {
        boldButton = document.getElementById("bold-btn");
        italicButton = document.getElementById("italic-btn");
        underlineButton = document.getElementById("underline-btn");
        textColorPicker = document.getElementById("text-color-picker");
        fillColorPicker = document.getElementById("fill-color-picker");
        fontSizeDropdown = document.getElementById("font-size-dropdown");
        alignDropdownButton = document.getElementById("align-dropdown-btn");
        alignLeftButton = document.getElementById("align-left-btn");
        alignCenterButton = document.getElementById("align-center-btn");
        alignRightButton = document.getElementById("align-right-btn");
        dropdownContent = document.querySelector(".dropdown-content");


        // Bold Button Action
        boldButton.addEventListener("click", function() {
            applyToSelectedCells(getSelectedCells, hot, (cell) => {
                let className = hot.getCellMeta(cell.row, cell.col).className || "";
                className = className.includes("htBold") ? className.replace("htBold", "") : className + " htBold";
                hot.setCellMeta(cell.row, cell.col, "className", className.trim());
            });
        });

        // Italic Button Action
        italicButton.addEventListener("click", function() {
            applyToSelectedCells(getSelectedCells, hot, (cell) => {
                let className = hot.getCellMeta(cell.row, cell.col).className || "";
                className = className.includes("htItalic") ? className.replace("htItalic", "") : className + " htItalic";
                hot.setCellMeta(cell.row, cell.col, "className", className.trim());
            });
        });

        // Underline Button Action
        underlineButton.addEventListener("click", function() {
            applyToSelectedCells(getSelectedCells, hot, (cell) => {
                let className = hot.getCellMeta(cell.row, cell.col).className || "";
                className = className.includes("htUnderline") ? className.replace("htUnderline", "") : className + " htUnderline";
                hot.setCellMeta(cell.row, cell.col, "className", className.trim());
            });
        });

        // Font Color Picker Action
        textColorPicker.addEventListener("change", function(event) {
            applyToSelectedCells(getSelectedCells, hot, (cell) => {
                let currentStyle = hot.getCellMeta(cell.row, cell.col).style || {};
                currentStyle.color = event.target.value;
                hot.setCellMeta(cell.row, cell.col, "style", currentStyle);
            });
        });

        // Background Fill Color Picker Action
        fillColorPicker.addEventListener("change", function(event) {
            applyToSelectedCells(getSelectedCells, hot, (cell) => {
                let currentStyle = hot.getCellMeta(cell.row, cell.col).style || {};
                currentStyle.backgroundColor = event.target.value;
                hot.setCellMeta(cell.row, cell.col, "style", currentStyle);
            });
        });

        // Font Size Dropdown Action
        fontSizeDropdown.addEventListener("change", function(event) {
            const selectedFontSize = event.target.value + "px";
            const lineHeightMapping = {
                12: "21px",
                14: "24px",
                18: "31px",
                24: "42px",
                36: "63px"
            };
            const selectedLineHeight = lineHeightMapping[event.target.value];
            applyToSelectedCells(getSelectedCells, hot, (cell) => {
                let currentStyle = hot.getCellMeta(cell.row, cell.col).style || {};
                currentStyle.fontSize = selectedFontSize;
                currentStyle.lineHeight = selectedLineHeight;
                hot.setCellMeta(cell.row, cell.col, "style", currentStyle);
            });
        });

        // Alignment Actions
        alignDropdownButton.addEventListener("click", function() {
            const dropdownContent = this.nextElementSibling;
            dropdownContent.style.display = dropdownContent.style.display === "none" ? "flex" : "none";
        });

        alignLeftButton.addEventListener("click", function() {
            applyToSelectedCells(getSelectedCells, hot, (cell) => {
                hot.setCellMeta(cell.row, cell.col, "className", "htLeft");
            });
            updateAlignmentIcon("htLeft");
            hideAlignmentDropdown();
        });

        alignCenterButton.addEventListener("click", function() {
            applyToSelectedCells(getSelectedCells, hot, (cell) => {
                hot.setCellMeta(cell.row, cell.col, "className", "htCenter");
            });
            updateAlignmentIcon("htCenter");
            hideAlignmentDropdown();
        });

        alignRightButton.addEventListener("click", function() {
            applyToSelectedCells(getSelectedCells, hot, (cell) => {
                hot.setCellMeta(cell.row, cell.col, "className", "htRight");
            });
            updateAlignmentIcon("htRight");
            hideAlignmentDropdown();
        });
    }

    return {
        initToolbar: initToolbar,
        updateAlignmentIcon: updateAlignmentIcon,
        hideAlignmentDropdown: hideAlignmentDropdown
    };
});
