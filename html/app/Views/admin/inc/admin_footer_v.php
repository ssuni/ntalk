<!-- Right bar overlay-->
<div class="rightbar-overlay"></div>

<!-- Vendor js -->
<script src="<?php echo base_url('assets/js/vendor.min.js');?>"></script>

<!-- knob plugin -->
<script src="<?php echo base_url('assets/libs/jquery-knob/jquery.knob.min.js');?>"></script>

<!--Morris Chart-->
<script src="<?php echo base_url('assets/libs/morris-js/morris.min.js');?>"></script>
<script src="<?php echo base_url('assets/libs/raphael/raphael.min.js');?>"></script>

<!-- Dashboard init js-->
<script src="<?php echo base_url('assets/js/pages/dashboard.init.js');?>"></script>

<!-- Plugins js -->
<script src="<?php echo base_url('assets/libs/moment/moment.js');?>"></script>
<script src="<?php echo base_url('assets/libs/x-editable/bootstrap-editable.min.js');?>"></script>
<!-- Sweet Alerts js -->
<script src="<?php echo base_url('assets/libs/sweetalert2/sweetalert2.min.js');?>"></script>
<!-- Ion Range Slider-->
<script src="<?php echo base_url('assets/libs/ion-rangeslider/ion.rangeSlider.min.js');?>"></script>
<!-- dropify js -->
<script src="<?php echo base_url('assets/libs/dropify/dropify.min.js');?>"></script>


<!-- Init js-->
<script src="<?php echo base_url('assets/js/mockjax.js');?>"></script>
<script src="<?php echo base_url('assets/js/pages/form-xeditable.init.js');?>"></script>
<!--<script src="--><?php //echo base_url('assets/js/pages/range-sliders.init.js');?><!--"></script>-->
<!--<script src="--><?php //echo base_url('assets/js/pages/sweet-alerts.init.js')?><!--"></script>-->
<script src="<?php echo base_url('assets/js/form.js');?>"></script>
<script src="<?php echo base_url('assets/js/require.js');?>"></script>
<script src="<?php echo base_url('assets/js/custom.js');?>"></script>
<!-- Modal-Effect -->
<script src="<?php echo base_url('assets/libs/custombox/custombox.min.js');?>"></script>
<script src="<?php echo base_url('assets/libs/switchery/switchery.min.js');?>"></script>
<!-- App js -->
<script src="<?php echo base_url('assets/js/app.min.js');?>"></script>
<!--<script type='text/javascript' data-main="--><?php //echo base_url('assets/js/custom.js');?><!--" src="--><?php //echo base_url('assets/js/require.js');?><!--"></script>-->

<script>
    $('[data-plugin="switchery"]').each(function (e, t) {
        new Switchery($(this)[0], $(this).data())
    })
</script>
</body>
</html>