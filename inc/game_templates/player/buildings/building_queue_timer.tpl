    var d = new Date();
    var value = Math.round(d.getTime() - {current_time} );
    buildTime = new Date({build_time} + value);
    urlTotal = urlBase + '&position={build_position_actual}&building={building_id}';
    $('#{build_item_timer_id}').countdown(
    {
        expiryText: completedText,
        until: buildTime,
        onExpiry: function(){ $('#{build_item_timer_id}_cancel').hide(); },
        expiryUrl: urlTotal,
        layout: timeLayout
    });