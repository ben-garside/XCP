function highlightOnLoad() {
  // Get search string
  if (/term\=/.test(window.location.search)) {
    var searchString = getSearchString();
    // Starting node, parent to all nodes you want to search
    var textContainerNode = document.getElementById("results");

    // Split search terms on '|' and iterate over resulting array
    var searchTerms = searchString.split('|');
    for (var i in searchTerms)    {
      // The regex is the secret, it prevents text within tag declarations to be affected
      var regex = new RegExp(">([^<]*)?("+searchTerms[i]+")([^>]*)?<","ig");
      highlightTextNodes(textContainerNode, regex, i);
    }

    // Create div describing the search
    var searchTermDiv = document.createElement("H2");
    searchTermDiv.className = 'searchterms';

    // Insert as very first child in searched node
    textContainerNode.insertBefore(searchTermDiv, textContainerNode.childNodes[0]);
  }
}

// Pull the search string out of the URL
function getSearchString() {
  // Return sanitized search string if it exists
  var rawSearchString = window.location.search.replace(/[a-zA-Z0-9\?\&\=\%\#]+term\=(\w+)(\&.*)?/,"$1");
  // Replace '+' with '|' for regex
  // Also replace '%20' if your cms/blog uses this instead (credit to erlando for adding this)
  return rawSearchString.replace(/\%20|\+/g,"\|");
}

function highlightTextNodes(element, regex, termid) {
  var tempinnerHTML = element.innerHTML;
  // Do regex replace
  // Inject span with class of 'highlighted termX' for google style highlighting
  element.innerHTML = tempinnerHTML.replace(regex,'>$1<span class="highlighted term'+termid+'">$2</span>$3<');
}

$(function() {
  highlightOnLoad();
  $( '#searchInput' ).val( getSearchString() )
  $( '.results' ).click(function(event) {
    var res = event.currentTarget;
    var xcp = $(res).find( '.xcp_id' ).text()
    window.open("http://xcp.dev/item.php?xcpid="+xcp, "_parent");
  });
});

