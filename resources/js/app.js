import "./bootstrap";

import Editor from "@toast-ui/editor";
import "@toast-ui/editor/dist/toastui-editor.css";

// The shared app bundle runs on every page, including pages without the
// Markdown editor. Only initialise it when all required elements exist.
const editorElement = document.querySelector("#editor");
const markdownContent = document.querySelector("#mdcontent");
const editorForm = document.querySelector("#mdeditor");

if (editorElement && markdownContent && editorForm) {
    const editor = new Editor({
        el: editorElement,
        height: "400px",
        initialEditType: "markdown",
        placeholder: "",
        initialValue: markdownContent.value,
        toolbarItems: [
            ["heading", "bold", "italic", "strike"],
            ["hr", "quote"],
            ["ul", "ol"],
        ],
    });

    editorForm.addEventListener("submit", (event) => {
        event.preventDefault();
        markdownContent.value = editor.getMarkdown();
        editorForm.submit();
    });
}
