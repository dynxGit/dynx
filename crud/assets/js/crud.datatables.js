/** -----------------------------------------------------------------------------------------------------
 * crud.datatable.js
 * 
 * @copyright Copyright &copy; Szincsák András
 * @author Szincsák András <andras@szincsak.hu>
 * @version 1.0
 * 
 * ------------------------------------------------------------------------------------------------------ */
$(function () {

    if (typeof DTconfig !== "undefined") {
        console.log(DTconfig);

        var DT = $('#DT').DataTable(DTconfig);

        DT.on('row-reorder', function (e, diff, edit) {
            var result = 'Reorder started on row: ' + edit.triggerRow.data()[2] + '<br>';
            for (var i = 0, ien = diff.length; i < ien; i++) {
                var rowData = DT.row(diff[i].node).data();

                result += rowData[1] + ' updated to be in position ' +
                    diff[i].newData + ' (was ' + diff[i].oldData + ')<br>';
            }
            console.log(edit);
            console.log(diff);
            $('#result').html('Event result:<br>' + result);
        });

        function DTevents() {
            $('#DT tfoot tr').addClass('filter').appendTo('#DT thead');
            $('#DT tfoot tr').remove();
            var root = ($("#DT").data('root'))
            $('#DT tr span.row_delete').css({
                "cursor": "pointer"
            }).on('click', function () {
                var id = ($(this).closest('tr').attr('id'));
                 Swal.fire({
                    title: DTcrudText.delete.title,
                    html: DTcrudText.delete.html,
                    icon:DTcrudText.delete.icon,
                    showCancelButton: true,
                    confirmButtonText: DTcrudText.buttons.confirm,
                    cancelButtonText: DTcrudText.buttons.cancel,
                    showLoaderOnConfirm: true,
                })
                    .then(function (result) {
                        if (result.isConfirmed) {
                            $.ajax("/" + root + "/remove/" + id, {
                                'method': 'DELETE',
                            }).done(function (xhr) {
                                DT.ajax.reload();
                            });
                        }
                    });
            });

            $('a.crudpopup').colorbox({
                'iframe': true,
                'maxWidth': '1200px',
                'maxHeight': '1000px',
                'width': '95%',
                'height': '90%',
                'current': "{current} / {total}",
                'onClosed': function () {
                    DT.ajax.reload();
                }
            });
            $('a.crudCreate').colorbox({
                'iframe': true,
                'maxWidth': '800px',
                'maxHeight': '600px',
                'width': '95%',
                'height': '90%',
                'onClosed': function () {
                    DT.ajax.reload();
                }
            });
            /**/




        };

        DT.on('draw', DTevents);
        DTevents();


        $(".DTfilter").on('click', function () {
            return false
        });

        $(window).on('resize', function () {
            console.log("Grid resize");
            $('#DT').DataTable().columns.adjust();
        });

        $(" .DTfilter").off('change keyup').on('change keyup', function () {
            var values = []
            $(".DTfilter").each(function (idx, el) {
                var dix = parseInt($(el).data('idx'));
                var term = values[dix] ? values[dix] : $(el).val();
                values[dix] = term;
                $('#DT').DataTable().column(dix).search(term ? term : "", false, true);

            });

            $('#DT').DataTable().draw();
            return false;
        });

        $('#DT').on('init.dt', function (e, settings, data) {
            $.each(data.columns, function (idx, el) {
                if (el.search.value) {
                    $('.DTfilter[name="filter_' + idx + '"]').val(el.search.value);
                }
            });
            $('#DT').DataTable().columns.adjust();
        });

        $('#DT').on('stateLoaded.dt', function (e, settings, column) {
            console.log('stateLoaded');

        });

        $('#DT').on('column-visibility.dt', function (e, settings, column, state) {
            console.log(settings.oAjaxData);
            $.each(settings.oAjaxData.columns, function (idx, el) {
                if (el.search.value) {
                    $('.DTfilter[name="filter_' + idx + '"]').val(el.search.value);
                }
            });
            $('#DT').DataTable().columns.adjust();
        });

        $('#DT').on('column-reorder', function (e, settings, details) {
            console.log(details);
            console.log('REORDERED');
            var headerCell = $(table.column(details.to).header());

        });
    }

});

