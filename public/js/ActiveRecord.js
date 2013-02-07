// Dependency of this library:
// ECMAScript 5: use the es5-shim in older browsers

var Class = {

    //---------- Inheritance API

    /**
     */
    extend: function (properties) {
        var superProto = this.prototype || Class;
        var proto = Object.create(superProto);
        // This method will be attached to many constructor functions
        // => must refer to "Class" via its global name (and not via "this")
        Class.copyOwnTo(properties, proto);
        
        var constr = proto.constructor;
        if (!(constr instanceof Function)) {
            throw new Error("You must define a method 'constructor'");
        }
        // Set up the constructor
        constr.prototype = proto;
        constr.super = superProto;
        constr.extend = this.extend; // inherit class method
        return constr;
    },

    /**
     */
    copyOwnTo: function(source, target) {
        Object.getOwnPropertyNames(source).forEach(function(propName) {
            Object.defineProperty(target, propName,
                Object.getOwnPropertyDescriptor(source, propName));
        });
        return target;
    }
};

var ActiveRecord = Class.extend({
    constructor: function () {
    	this.name = this.name || typeof this;
    },
	implementFind: function(sMethod,sWhere, aBindVars, fCallBack){
		$.getJSON("db", {
			action : sMethod,
			object : this.name,
			where  : sWhere,
			bindvars: aBindVars.join(",")
		}, fCallBack);
	},
	find: function(sWhere, aBindVars, fCallBack){
		var that = this;
		this.implementFind('find', sWhere, aBindVars, function(data){
			var aRc = new Array();
			$.each(data.items, function(i, item) {
				item.name = that.name;
				aRc.push((new (ActiveRecord.extend(item))));
			});
			fCallBack.call(that, aRc);
		});
	},
	load: function(sWhere, aBindVars, fCallBack){
		var that = this;
		this.implementFind('load', sWhere, aBindVars, function(data){
			$.each(data, function(key, value){ that[key] = value});
			fCallBack.call(that);
		});
	},
	implementSaveDelete: function(sMethod, fCallBack){
		var that = this;
		var oParams = new Object(); 
		oParams.action = sMethod;
		oParams.object = this.name;
		$.each(this, function(key, value){
			if(that.hasOwnProperty(key)){
				oParams[key] = value;
			}
		});
		$.getJSON("db", oParams, function(data){
			$.each(data, function(key, value){ 
				that[key] = value;
			});
			fCallBack.call(that);
		});
	},
	save: function(fCallBack){
		this.implementSaveDelete('save', fCallBack);
	},
	'delete': function(fCallBack){
		this.implementSaveDelete('delete', fCallBack);		
	},
    describe: function() {
        return this.name;
    }
});
