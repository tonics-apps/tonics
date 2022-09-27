/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

let getAllTonicsFieldTabContainer = document.querySelectorAll('.tonicsFieldTabsContainer');
if (getAllTonicsFieldTabContainer){
    getAllTonicsFieldTabContainer.forEach(eachTabContainer => {
        if (eachTabContainer.id){
            let parentID = eachTabContainer.id;
            // get all items from session and check it...
            let tonicsTabFieldIDLocalStorage = localStorage.getItem(`tonicsTabFieldID_${parentID}`);
            if (tonicsTabFieldIDLocalStorage){
                let tabID = eachTabContainer.querySelector(`#${tonicsTabFieldIDLocalStorage}`);
                if (tabID?.tagName.toString() === 'INPUT' && tabID?.parentElement === eachTabContainer){
                    tabID.checked = true;
                }
            }

            eachTabContainer.addEventListener('click', (e) => {
                let el = e.target;
                if (el?.tagName.toString() === 'INPUT' && el?.parentElement === eachTabContainer){
                    let inputID = el.id;
                    // set local storage item
                    localStorage.setItem(`tonicsTabFieldID_${parentID}`, inputID);
                }
            });
        }
    });
}

