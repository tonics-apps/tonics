
/*
 *     Copyright (c) 2024. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

let containerForSelection = document.querySelector('[data-container_for_selection="true"]');
    let singleFileStringName = '[data-list_id]';
    let shiftClick = new Map();

function unHighlightFile(file) {
    file.classList.remove('selected-file');
    file.dataset.selected = 'false';
}

function highlightFile(file) {
    file.classList.add('selected-file');
    file.dataset.selected = 'true';
}

function resetPreviousFilesState() {
    document.querySelectorAll(singleFileStringName).forEach(el => {
        el.classList.remove('selected-file');
        el.setAttribute('data-selected', 'false');
    });

    if (document.querySelector('[data-simulate_shift_key="true"]')){
        document.querySelector('[data-simulate_shift_key="true"]').dataset.simulate_shift_key = 'false';
    }

    if (document.querySelector('[data-simulate_ctrl_key="true"]')){
        document.querySelector('[data-simulate_ctrl_key="true"]').dataset.simulate_ctrl_key = 'false';
    }

}

function resetShiftClick() {
    shiftClick = new Map();
}

function setShiftClick(file) {
    highlightFile(file);
    let id = file.dataset.list_id;

    // remove file that have previously been set, so, they can be pushed below
    if (shiftClick.get(id)) {
        shiftClick.delete(id);
    }

    shiftClick.set(id, file);
    if (shiftClick.size >= 2) {
        // this is getting the first and last shift clicked item, and we're sorting the integer
        let firstItem = [...shiftClick][0][0],
            lastItem = [...shiftClick][shiftClick.size - 1][0],
            listIDToLoop = [firstItem, lastItem];
        listIDToLoop.sort();

        // loop over the sorted ranges. and highlight 'em
        for (let i = listIDToLoop[0]; i <= listIDToLoop[1]; i++) {
            // highlight file
            let file = document.querySelector(`[data-list_id="${i}"]`);
            if (file) {
                highlightFile(file);
            }
        }
    }
}

if (containerForSelection){
    containerForSelection.addEventListener('click', (e) => {
        let el = e.target;
        // e.preventDefault();
        if (el.closest(singleFileStringName)) {
            e.stopPropagation();
            let file = el.closest(singleFileStringName);

            if (document.querySelector('[data-simulate_ctrl_key="true"]')){
                (file.classList.contains('selected-file')) ? unHighlightFile(file) : highlightFile(file);
                return false;
            }

            if (document.querySelector('[data-simulate_shift_key="true"]')){
                setShiftClick(file);
                return false;
            }

            // if this is a ctrlKey, we assume, the user wanna select multiple files
            if (e.ctrlKey) {
                (file.classList.contains('selected-file')) ? unHighlightFile(file) : highlightFile(file);
                return false;
            }
            // shift clicking, selecting in ranges
            else if (e.shiftKey) {
                // reset previous state
                resetPreviousFilesState()
                setShiftClick(file);
            } else {
                // this is a norm mouse click
                resetPreviousFilesState();
                highlightFile(file);

                // for shift key
                resetShiftClick();
                setShiftClick(file);
            }
        } else {
            resetShiftClick();
            resetPreviousFilesState();
        }
    });

    containerForSelection.addEventListener('dblclick', (e) => {
        let el = e.target;
        if (el.closest(singleFileStringName)) {
            let file = el.closest(singleFileStringName);
            let link = file.dataset.db_click_link;
            if (link) {
                window.location.href = link;
            }
        }
    });

    containerForSelection.addEventListener('keydown', (e) => {
        let el = e.target;
        if (el.closest(singleFileStringName)) {
            let file = el.closest(singleFileStringName);
            switch (e.code) {
                case 'Enter':
                    highlightFile(file);
                    navigateEnter(file);
                    break;
            }
        }
    });
}

function navigateEnter(file) {
    let link = file.dataset.db_click_link;
    if (link) {
        window.location.href = link;
    }
}


function getSelectedFile() {
    return document.querySelector('[data-selected="true"]');
}