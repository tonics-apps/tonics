hookTinyMCE();
function hookTinyMCE() {
    if (typeof tinymce !== 'undefined') {
        let allTinyArea = document.querySelectorAll('.tinyMCEBodyArea');
        allTinyArea.forEach(tinyArea => {
            tinyArea.dataset.tinyinstance = 'true';
            tinyArea.id = 'tinyMCEBodyArea' + new Date().valueOf();
            addTiny('#' +tinyArea.id);
        });

        const tinyDialogObserver = new MutationObserver(((mutationsList, observer) => {
            for (const mutation of mutationsList) {
                // added nodes.
                let addedNode = mutation.addedNodes[0];
                if (mutation.addedNodes.length > 0 && addedNode.nodeType === Node.ELEMENT_NODE) {
                    let tinyArea = addedNode.querySelector('.tinyMCEBodyArea');
                    if (tinyArea) {
                        // if tinyInstance is available, re-initialize it
                        if (tinyArea.dataset.tinyinstance === 'true'){
                            let allTinyArea = document.querySelectorAll('.tinyMCEBodyArea');
                            allTinyArea.forEach(tinyArea => {
                                tinymce.execCommand("mceRemoveEditor", false, tinyArea.id);
                                tinyArea.id = 'tinyMCEBodyArea' + new Date().valueOf();
                                addTiny('#' +tinyArea.id);
                            });
                            return;
                        }

                        // else...
                        tinyArea.dataset.tinyinstance = 'true';
                        tinyArea.id = 'tinyMCEBodyArea' + new Date().valueOf();
                        addTiny('#' +tinyArea.id);
                    }
                }
            }
        }));
        // Start observing the target node for configured mutations
        tinyDialogObserver.observe(document.querySelector('main'), {attributes: false, childList: true, subtree: true});
    }
}

function addTiny(editorID) {
    tinymce.init({
        // add support for image lazy loading
        extended_valid_elements : "img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name|loading=lazy|decoding=async]",
        selector: editorID,
        height: 800,
        menubar: true,
        plugins: [
            'advlist', 'tonics-drivemanager', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview', 'anchor',
            'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount',
        ],
        toolbar: 'undo redo | tonics-drivemanager link image media | ' +
            'bold italic backcolor | alignleft aligncenter ' +
            'alignright alignjustify | bullist numlist | ' +
            'removeformat | help',
        content_style: 'body { font-family:IBMPlexSans-Regular,Times New Roman,serif; font-size:20px }',
        contextmenu: "link image | copy searchreplace tonics-drivemanager | bold italic blocks align",
        setup: function (editor) {
            editor.on('init change blur', function (e) {
                 tinymce.triggerSave();
            });
        }
    });
}