<x-filament::page>
    <div id="calendar" style="max-width: 900px; margin: 40px auto; height: 600px;"></div>
</x-filament::page>

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
    <style>
        #calendar {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            padding: 10px;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"></script>
    <script>
        window.eventos = @json($eventos);
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: window.eventos,
                eventClick: function(info) {
                    alert('Motivo: ' + info.event.title + '\nFecha: ' + info.event.start);
                },
            });
            calendar.render();
        });
    </script>
@endpush
