(function () {
    "use strict";

    $(".full-calendar").each(function () {
        const el = $(this);
        const calendarEl = el.children()[0];

        const eventsUrl = el.data("events-url");
        const locale = el.data("locale") || "en";

        let calendar = new Calendar(calendarEl, {
            plugins: [
                interactionPlugin,
                dayGridPlugin,
                timeGridPlugin,
                listPlugin,
            ],
            droppable: true,
            locale: locale,
            headerToolbar: {
                left: "prev,next today",
                center: "title",
                right: "dayGridMonth,timeGridWeek,timeGridDay,listWeek",
            },
            navLinks: true,
            editable: true,
            dayMaxEvents: true,

            // 🔥 AQUÍ ESTÁ LA CLAVE
            events: eventsUrl ?? [],

            drop: function (info) {
                if (
                    $("#checkbox-events").length &&
                    $("#checkbox-events")[0].checked
                ) {
                    $(info.draggedEl).parent().remove();

                    if ($("#calendar-events").children().length == 1) {
                        $("#calendar-no-events").removeClass("hidden");
                    }
                }
            },
            eventClick: function (info) {
            info.jsEvent.preventDefault(); // ⛔ evita redirección

            const event = info.event;

            openEventModal({
                title: event.title,
                start: event.start,
                end: event.end,
                description: event.extendedProps.description,
                address: event.extendedProps.address,
                city: event.extendedProps.city,
                publicLink: event.extendedProps.publicLink,
            });
        },
        });

        calendar.render();
    });
})();
