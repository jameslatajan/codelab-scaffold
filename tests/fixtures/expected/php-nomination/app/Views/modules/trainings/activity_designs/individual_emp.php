<table class="table table-borderless table-sm mb-0 table-secondary align-middle">
    <tr>
        <td class="wx-100">
            Find Employee
        </td>
        <td>
            <select id="empID" class="form-control form-control-sm wx-400 select2-emp" data-placeholder="Enter ID No or Name">
                <option value="">&nbsp;</option>
            </select>
            <button id="btnAdd" type="button" class="btn btn-sm btn-primary rounded-pill"><i class="fas fa-plus"></i> Add</button>
        </td>
    </tr>
</table>

<table class="table table-borderless table-hover table-sm mb-0">
    <thead>
        <tr class="header-bg">
            <th class="text-center wx-100">Action</th>
            <th class="wx-120">ID No.</th>
            <th>Employee Name</th>
        </tr>
    </thead>
    <tbody id="empSearchBody">
        <?php for ($i = 0; $i < 5; $i++) { ?>
            <tr>
                <td colspan="3">&nbsp;</td>
            </tr>
        <?php } ?>
    </tbody>
</table>