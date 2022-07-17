try {
    if (tonicsErrorMessages instanceof Array && tonicsErrorMessages.length > 0){
        tonicsErrorMessages.forEach((value) => {
            myModule.errorToast(value, 6000);
        });
    }

    if (tonicsInfoMessages instanceof Array && tonicsInfoMessages.length > 0){
        tonicsInfoMessages.forEach((value) => {
            myModule.infoToast(value, 6000);
        });
    }

    if (tonicsSuccesssMessages instanceof Array && tonicsSuccesssMessages.length > 0){
        tonicsSuccesssMessages.forEach((value) => {
            myModule.successToast(value, 6000);
        });
    }

} catch (e) {
   // console.log(e.toLocaleString());
}