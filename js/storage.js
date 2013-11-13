storageType = function() {
	this.testStorage = function() {
		if (typeof(Storage) == 'undefined') {
			return false;
		}
		return true;
	}

	this.drawStorageTest = function() {
		if (this.testStorage()) {
			return;
		}
		
		o_popup_notifications.addNotification("We can't save classes on your computer. Your browser doesn't support <a target='_blank' href='http://www.w3schools.com/html/html5_webstorage.asp'>local storage</a>. Please update or use a different <a target='_blank' href='http://www.w3schools.com/browsers/default.asp'>browser</a>.");
	}
	
	this.storeData = function(key, data) {
		if (!this.testStorage()) return false;
		var sdata = this.retrieveData(key);
		if (sdata !== null) {
			data = this.mergerData(data, sdata);
		}
		localStorage[key] = JSON.stringify({type:typeof(data), data:data});
		return true;
	}
	
	this.retrieveData = function(key) {
		if (!this.testStorage()) return false;
		var data = localStorage[key];
		if (typeof(data) == 'undefined') {
			return null;
		}
		data = JSON.parse(data);
		return data.data;
	}
	
	this.getGuestData = function(key) {
		if (getUsername() != "guest") {
			return null;
		}
		var gdata = this.retrieveData("guest");
		if (gdata === null) {
			return null;
		}
		if (typeof(gdata[key]) == "undefined") {
			return null;
		}
		return gdata[key];
	}

	this.setGuestData = function(key, data) {
		if (getUsername() != "guest") {
			return true;
		}
		var gdata = this.retrieveData("guest");
		if (gdata === null) {
			gdata = {};
		}
		gdata[key] = data;
		return this.storeData("guest", gdata);
	}
	
	this.mergerData = function(data1, data2) {
		var mergerData = this.mergerData;
		var mergeData = function(k,v) {
			if (typeof(data2[k]) == "object" && typeof(v) == "object") {
				data1[k] = mergerData(data1[k], data2[k]);
			}
		}
		var addData = function(k,v) {
			if (typeof(data1[k]) == 'undefined') {
				data1[k] = v;
			}
		}
		$.each(data1, mergeData);
		$.each(data2, addData);
		return data1;
	}
}

o_storage = new storageType();