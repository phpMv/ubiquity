let status=$("#{{id}} tbody tr:last-child ._status");

if($("#{{id}} tr:last-child .menu a").length>$("#{{id}} tbody tr").length){
    let selector="#{{id}} tbody tr";
    let clone=$(selector).last().clone(false);
    clone.find( "[id]" ).each( function() { 
        let strNewId = $( this ).attr( "id" ).replace( /\d+$/, function( strId ) { return parseInt( strId ) + 1; } );$( this ).attr( "id", strNewId );
    });
    $(selector).last().after(clone);
    updateCmb();
    $("#{{id}} tbody tr:last-child ._status").val("added");
    $("#{{id}} tbody tr:last-child .dropdown").dropdown("toggle").dropdown("clear",true);
}
