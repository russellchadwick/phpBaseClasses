var xmlHttp = createXmlHttpRequestObject ();
var showErrors = true;
var startedLoop = false;
var cache = new Array ();

function createXmlHttpRequestObject () {
  var xmlHttp;

  try {
    xmlHttp = new XMLHttpRequest ();
  } catch (e) {
    var XmlHttpVersions = new Array("MSXML2.XMLHTTP.6.0",
                                    "MSXML2.XMLHTTP.5.0",
                                    "MSXML2.XMLHTTP.4.0",
                                    "MSXML2.XMLHTTP.3.0",
                                    "MSXML2.XMLHTTP",
                                    "Microsoft.XMLHTTP");

    for (var i = 0; i < XmlHttpVersions.length && !xmlHttp; i++) {
      try { 
        xmlHttp = new ActiveXObject (XmlHttpVersions[i]);
      } catch (e) {
      }
    }
  }

  if (!xmlHttp) {
    displayError("Error creating the XMLHttpRequest object.");
  } else {
    return xmlHttp;
  }
}

function displayError ($message) {
  if (showErrors) {
    showErrors = false;
    alert ("Error encountered: \n" + $message);
  }
}

function queueRequest (URL, params) {
  if (trim (request.value) != "") {
    params = "mode=SendAndRetrieveNew" + "&id=" + encodeURIComponent(lastMessageID);
    cache.push (URL);
    cache.push (params);
  }

  if (!startedLoop) {
    startedLoop = true;
    processQueue ();
  }
}

function processQueue () {
  if (xmlHttp) {
    if (cache.length > 0) {
      try {
        if ((xmlHttp.readyState == 4) || (xmlHttp.readyState == 0)) {
          URL = cache.shift();
          params = cache.shift();

          xmlHttp.open ("POST", URL, true);
          xmlHttp.setRequestHeader ("Content-Type", "application/x-www-form-urlencoded");
          xmlHttp.onreadystatechange = handleRequestStateChange;
          xmlHttp.send (params);
        } else {
     	    setTimeout ("processQueue ();", 100);
      } catch(e) {
        displayError (e.toString ());
      }
    } else {
    	startedLoop = false;
    }
  }
}

function handleRequestStateChange () {
  if (xmlHttp.readyState == 4) {
    if (xmlHttp.status == 200) {
      try {
        readResponse ();
      } catch (e) {
        displayError (e.toString ());
      }
    } else {
      displayError (xmlHttp.statusText);
    }
  }
}

// read server's response 
function readResponse () {
  var response = xmlHttp.responseText;

  if (response.indexOf("ERRNO") >= 0 || response.indexOf("error:") >= 0 || response.length == 0) {
    throw (response.length == 0 ? "Server error." : response);
  }

  alert (response);

  setTimeout ("processQueue ();", 100);
}