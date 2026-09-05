<div class="container">
    <h3 style="text-align: center;"><?php echo $title ?></h3>
    <table>
        <thead>
            <tr class="header-bg">
                <?php echo $this->include('components/print_table_header'); ?>
            </tr>
        </thead>
        <tbody>
            <?php echo $this->include('components/print_table_body'); ?>
        </tbody>
    </table>
</div>