
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

const EventsConfig = {

    OnBeforeTonicsFieldPreviewEvent: [],
    OnBeforeTonicsFieldSubmitEvent: [],

    //  OtherEvent: [],
    // DataTables Event
    OnBeforeScrollBottomEvent: [],
    OnScrollBottomEvent: [],
    OnDoubleClickEvent: [],
    OnClickEvent: [],
    OnShiftClickEvent: [],
    OnRowMarkForDeletionEvent: [],

    OnSubmitFieldEditorsFormEvent: [],

    // Event For Audio Player
    OnAudioPlayerPlayEvent: [],
    OnAudioPlayerPauseEvent: [],
    OnAudioPlayerPreviousEvent: [],
    OnAudioPlayerNextEvent: [],
    OnAudioPlayerClickEvent: [],
    OnAudioPlayerPaymentGatewayCollatorEvent: [],
};

window.TonicsEvent.EventConfig = EventsConfig;


