ActiveRecord
============

Javascript and php ActiveRecord with Oauth2 to google implementation

			var Person = ActiveRecord.extend({constructor: function(){}, name: 'person'});		
			var oPerson = new Person();
	//C(reate)
			oPerson.lname = 'Hildred';
			oPerson.fname = 'Rich';
			oPerson.save(fcomplete); //fcomplete is a function to be called when the save is complete
			
	//R(ead)
			oPerson.find('lname = ?', ['Hildred'], fcomplete); // finds all occurences of People with lname = 'Hildred' and calls fcomplete when done
			oPerson.load('lname = ?', ['Hildred'], fcomplete); // finds one occurence of Person with lname = 'Hildred' and calls fcomplete when done

	//U(pdate)
			oPerson.fname = 'Richard';
			oPerson.save(fcomplete); //fcomplete is a function to be called when the save is complete
			
	//D(elete)
			oPerson.delete(fcomplete); //fcomplete is a function to be called when the delete is complete

See Also docs/ActiveRecord.pdf
	
