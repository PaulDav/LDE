var tabStrips = new Array();                           

    function initTab() {
    	
        var UrlHash = window.location.hash.substr(1);
        var InitSelection = 0;

    	var tabLinks = new Array();
  	    var contentDivs = new Array();

  	  	var tabStrip = document.getElementById('tabs');
  		if (tabStrip != null){
	        var tabListItems = tabStrip.childNodes;
	              
	        for ( var i = 0; i < tabListItems.length; i++ ) {
	          if ( tabListItems[i].nodeName == "LI" ) {
	            var tabLink = getFirstChildWithTagName( tabListItems[i], 'A' );
	            var id = getHash( tabLink.getAttribute('href') );
	            tabLinks[id] = tabLink;
	            contentDivs[id] = document.getElementById( id );
	
	  			if (id == UrlHash) InitSelection = i;
	            
	          }
	        }
  		}

        tabStrips.push(new Array());

        tabStrips[tabStrips.length-1]['links'] = tabLinks;
        tabStrips[tabStrips.length-1]['divs'] = contentDivs;
          
        
        var strips = document.getElementsByClassName('tabstrip');
        t = strips.length;

        while(t--) {
      	  
      	    var tabLinks = new Array();
      	    var contentDivs = new Array();
            
            var tabListItems = strips[t].childNodes;

            for ( var i = 0; i < tabListItems.length; i++ ) {
                if ( tabListItems[i].nodeName == "LI" ) {
                  var tabLink = getFirstChildWithTagName( tabListItems[i], 'A' );
                  var id = getHash( tabLink.getAttribute('href') );
                  tabLinks[id] = tabLink;
                  contentDivs[id] = document.getElementById( id );

        			if (id == UrlHash) InitSelection = i;
                  
                }
            }
            
            tabStrips.push(new Array());
  	          
  	        tabStrips[tabStrips.length-1]['links'] = tabLinks;
  	        tabStrips[tabStrips.length-1]['divs'] = contentDivs;
            
        }



  	  for (var t = 0; t < tabStrips.length; t++) {
        
  	      var i = 0;
  	
  	      for ( var id in tabStrips[t]['links'] ) {
  	    	tabStrips[t]['links'][id].onclick = showTab;
  	    	tabStrips[t]['links'][id].onfocus = function() { this.blur() };
  	        if ( i == InitSelection ) tabStrips[t]['links'][id].className = 'selected';
  	        i++;
  	      }
  	
  	      var i = 0;
  	
  	      for ( var id in tabStrips[t]['divs'] ) {
  	        if ( i != InitSelection ) tabStrips[t]['divs'][id].className = 'tabContent hide';
  	        if ( i == InitSelection ) tabStrips[t]['divs'][id].className = 'tabContent';
  	        i++;
  	      }
  	  }
  	  
    }    	


    function showTab() {

      var selectedId = getHash( this.getAttribute('href') );
  	  for (var t = 0; t < tabStrips.length; t++) {
		var found = false;
      	for ( var id in tabStrips[t]['divs'] ) {
      		if ( id == selectedId ) {
      			found = true;
      	    }
      	}
     	if ( found == true ){     
   			for ( var id in tabStrips[t]['divs'] ) {
  		    	if ( id == selectedId ) {
      		    	tabStrips[t]['links'][id].className = 'selected';
     	        	tabStrips[t]['divs'][id].className = 'tabContent';
      	        } else {
      		    	tabStrips[t]['links'][id].className = '';
					tabStrips[t]['divs'][id].className = 'tabContent hide';
      		    }
      	    }
   			
  		    return false  	    
      	}
  	  }

      return false;
    }


       

    function getFirstChildWithTagName( element, tagName ) {
      for ( var i = 0; i < element.childNodes.length; i++ ) {
        if ( element.childNodes[i].nodeName == tagName ) return element.childNodes[i];
      }
    }

    function getHash( url ) {
      var hashPos = url.lastIndexOf ( '#' );
      return url.substring( hashPos + 1 );
    }
