<?php
if (Auth::user()->branchId != null) {
    $_branchName = Auth::user()->branch->branchName;
} else {
    $_branchName = '';
}
?>
<script type="text/javascript">
    $(document).ready(function () {

        //Buttons examples
        $('#datatable').DataTable({

            dom: 'Bfrtip',
            order: [],
            "pageLength": 20,
            "lengthMenu": [[20, 30, 50, -1], [20, 30, 50, "All"]],
            "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
            //debugger;
            var index = iDisplayIndexFull + 1;
//            $("td:first", nRow).html(index);
            return nRow;
            },
            buttons: ['pageLength','excel','pdf',
                {
                    extend: 'print',
                    text: 'Print All',
                    autoPrint: true,
                    exportOptions: {
                        columns: [':not(.hidden-print)']
                    },

                    messageTop: function () {
                        return '<h2 class="text-center">{{ $_branchName }}</h2>'
                    },
                    messageBottom: 'Print: {{ date("d-M-Y") }}',
                    customize: function (win) {

                        $(win.document.body).find('h1').css('text-align', 'center');
                        $(win.document.body).find('table')
                            .removeClass('table-striped table-responsive-sm table-responsive-lg dataTable')
                            .addClass('compact')
                            .css('font-size', 'inherit', 'color', '#000');

                    }
                },
                {
                    extend: 'print',
                    text: 'Print',
                    autoPrint: true,
                    exportOptions: {
                        columns: [':not(.hidden-print)'],
                        modifier: {
                            page: 'current'
                        }
                    },

                    messageTop: function () {
                        return '<h2 class="text-center">{{ $_branchName }}</h2>'
                    },
                    messageBottom: 'Print: {{ date("d-M-Y") }}',
                    customize: function (win) {

                        $(win.document.body).find('h1').css('text-align', 'center');
                        $(win.document.body).find('table')
                            .removeClass('table-striped table-responsive-sm table-responsive-lg dataTable')
                            .addClass('compact')
                            .css('font-size', 'inherit', 'color', '#000');

                    }

                }

            ]
        });
    });
</script>
