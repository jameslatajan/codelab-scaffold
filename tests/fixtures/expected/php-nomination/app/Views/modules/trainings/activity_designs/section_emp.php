<table class="table table-sm table-secondary mb-0">
    <tbody>
        <tr>
            <td class="align-middle wx-100"><label for="secID" class="form-label mb-0">Section <small class="text-danger">*</small></label></td>
            <td>
                <select class="form-select form-select-sm select2-section wx-400" id="secID" name="secID" required title="Section">
                    <option value="">&nbsp;</option>
                    <?php foreach ($sections as $sec) { ?>
                        <option value="<?php echo $encrypter->encode($sec->secID); ?>"><?php echo $sec->secAbbr . ' - ' . $sec->sectionName; ?></option>
                    <?php } ?>
                </select>
            </td>
        </tr>
    </tbody>
</table>

<table class="table table-borderless table-hover table-sm mb-0">
    <thead>
        <tr class="header-bg">
            <th class="wx-100"><input type="checkbox" class="form-check-input check-all" style="width: 20px; height: 20px;"></th>
            <th class="wx-120">ID No.</th>
            <th>Employee Name</th>
        </tr>
    </thead>
    <tbody id="sectionEmployeesBody">
        <?php for ($i = 0; $i < 5; $i++) { ?>
            <tr>
                <td colspan="3">&nbsp;</td>
            </tr>
        <?php } ?>
    </tbody>
</table>