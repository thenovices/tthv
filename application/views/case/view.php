<h2>Patient Details</h2>
    <div id="check_in"></div>
<div class="post">
	<div class="entry">
		<label>Patient Name: <b><?php echo $case['patient_name']; ?></b></label>
		<label>Village Name: <b><?php echo $case['village_name']; ?></b></label>
		<label>Primary Health Center: <b><?php echo $case['phc_name']; ?></b></label>
		<label>Mobile: <b><?php echo $case['mobile']; ?></b></label>
    <label>Clinic Access: <b><?php echo ucfirst($case['clinic_access']); ?></b></label>
    <label>Location: <b><?php echo $case['location']; ?></b></label>
	</div>

	<div class="entry">
		<h3 class="title">Add Child</h3>

		<?php if ($errors): ?>
		<div class="error">
			<ul>
			<?php foreach($errors as $error): ?>
			<li><?php echo $error; ?> </li>
			<?php endforeach; ?>
			</ul>
		</div>
		<?php endif; ?>

		<?php
		echo form::open();

		echo form::label('child_name', 'Child Name');
		echo form::input('child_name');

		echo form::label('birth_date', 'Birth Date (Actual or Expected)');
		echo form::input('birth_date', NULL, array('id' => 'birth_date'));

		echo form::submit('submit', 'Add Child');
		echo form::close();
		?>
	</div>

	<div class="entry">
  <h3 class="title">Upcoming Appointments <?php echo html::anchor("appointment/add/{$case['id']}", html::image('images/add.png',
    array('height' => '20px'))); ?></h3>

<?php echo html::anchor("appointment/all/{$case['id']}", "View all appointments"); ?>

		<table id="appointments" class="view-table">
			<thead><tr>
				<th>Date</th>
				<th>Child Name</th>
				<th>Treatment</th>
			</tr></thead>
			<tbody>
			<?php foreach($appts as $appt): ?>
        <tr appt_id="<?php echo $appt['id']; ?>"><td><?php echo date('M j, Y', $appt['date']); ?></td>
				<td><?php echo $appt['child_name']; ?></td>
				<td><?php echo $appt['treatment']; ?></td>
        <td><a href="#" onclick="return false;" appt_id="<?php echo $appt['id']; ?>"><?php echo html::image('images/delete.png', array('width' => '20px')); ?></a></td>
        <td><span class="sure" appt_id="<?php echo $appt['id']; ?>" style="display:none;">Sure? <span class="yes" style="font-weight: bold;">Yes</span> <span class="no" style="font-weight: bold;">No</span></span></td></tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>

<script>
	$(function() {
    // Setting up JS hooks for deleting
    $("#appointments a").each(function(i) {
      $(this).click(function() {
        var apptId = $(this).attr('appt_id');
        $(".sure[appt_id=" + apptId + "]").fadeIn();
      });
    });

    $(".yes").each(function(i) {
      $(this).click(function() {
        var apptId = $(this).parent().attr('appt_id');
        $.post('<?php echo url::site("ajax/delete_appointment"); ?>', {id: apptId}, function(data) {
          if (data == "success") {
            $("tr[appt_id=" + apptId + "]").fadeOut();
          }
        });
      });
    });

    $(".no").each(function(i) {
      $(this).click(function() {
        var apptId = $(this).parent().attr('appt_id');
        $(".sure[appt_id=" + apptId + "]").fadeOut();
      });
    });

    // Datepicker
		$("#birth_date").datepicker();

    // Checkin functionality
    function checkIn() {
      $("#check_in").empty();
      $("<span />").addClass("checked-in").append("Checked In!").appendTo($("#check_in"));
    }

    var checkedIn = <?php echo $checkedIn; ?>;
    if (checkedIn == 0) {
      $("#check_in").empty();
      $("#check_in").append('<?php echo html::anchor("#", "Check In", array("id" => "check_in")); ?>');
      $("#check_in").click(function() {
        $.post('<?php echo url::site("ajax/check_in"); ?>', {id: <?php echo ($checkedIn == -1) ? 0 : $nextAppt['id']; ?>}, function(data) {
          if (data == "success") {
            $("#check_in").unbind('click');
            checkIn();
          }
        });
      });
    }
    else if (checkedIn == 1) {
      checkIn();
    }
	});
</script>
