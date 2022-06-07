try {
    new myModule.MenuToggle('.site-nav', new myModule.Query())
        .settings('.menu-block', '.dropdown-toggle', '.child-menu')
        .buttonIcon('#tonics-arrow-up', '#tonics-arrow-down')
        .menuIsOff(["swing-out-top-fwd", "d:none"], ["swing-in-top-fwd", "d:flex"])
        .menuIsOn(["swing-in-top-fwd", "d:flex"], ["swing-out-top-fwd", "d:none"])
        .closeOnClickOutSide(true)
        .run();
}catch (e) {
    console.error("An Error Occur Setting MenuToggle: Site-Nav")
}