function setDatePicker(){
	$(function() {
		$('input').filter('.datepicker').datepicker({ dateFormat: 'dd/mm/yy' });
	});
}