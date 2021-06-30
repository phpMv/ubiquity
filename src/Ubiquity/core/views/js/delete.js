let statusField = $(this).closest("tr").find("input._status");

if (statusField.val() != "deleted") {
    if (statusField.val() == "added") {
        if (!$(this).closest("tr").is(":last-child")) {
            let lastInput=$(this).closest('tbody').find('tr:last-child input').first();
            $(this).closest("tr").remove();
            updateCmb();
        } else {
            $(this).closest("tr").find(".dropdown").dropdown("toggle").dropdown("clear");
            $(this).closest("tr").find("input").val("");
            statusField.val("added");
        }
    } else {
        $(this).closest("tr").find(".text, input").css("text-decoration", "line-through");
        statusField.val("deleted");
        $(this).toggleClass('red');
        $(this).find('i.icon').toggleClass('remove undo');
    }
} else if(statusField.val() != "added") {
    statusField.val("");
    $(this).closest("tr").find(".text, input").css("text-decoration", "none");
    $(this).toggleClass('red');
    $(this).find('i.icon').toggleClass('remove undo');
}
