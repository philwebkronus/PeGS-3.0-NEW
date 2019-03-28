function clock() {
	var aWeekDayName = new Array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
	var aMonthName = new Array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December")
	var dtNow = new Date(dCurrentDate);
	var sHour, sMinute, sSecond, sTimeOfDay = "AM";

	dtNow = new Date(dtNow.setSeconds(dtNow.getSeconds() + 1));

	dCurrentDate = dtNow;

	sHour = dtNow.getHours();
	sMinute = dtNow.getMinutes();
	sSecond = dtNow.getSeconds();

	if (parseInt(sHour) >= 12) {
		if (parseInt(sHour) > 12)

			sHour = parseInt(sHour)-12;

		sTimeOfDay = "PM"
	}

	if (parseInt(sHour) == 0)
		sHour = 12;

	if (parseInt(sHour) < 10)
		sHour = "0" + sHour;

	if (parseInt(sMinute) < 10)
		sMinute = "0" + sMinute;

	if (parseInt(sSecond) < 10)
		sSecond = "0" + sSecond;

	document.getElementById("lblDate").innerHTML = aWeekDayName[dtNow.getDay()] + ", " + aMonthName[dtNow.getMonth()] + " " + dtNow.getDate() + ", " + dtNow.getFullYear();
	document.getElementById("lblDate").title = "Current server date";

	document.getElementById("lblTime").innerHTML = sHour + ":" + sMinute + ":" + sSecond + " " + sTimeOfDay;
	document.getElementById("lblTime").title = "Current server time";
	
	window.setTimeout("clock();", 1000);
}