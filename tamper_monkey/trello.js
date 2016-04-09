// ==UserScript==
// @name         trello todo task analysis
// @namespace    https://trello.com/b/TQGU6Sed/detail-todo
// @version      0.1
// @description  
// @author       Felix Ling
// @match        https://trello.com/b/TQGU6Sed/detail-todo
// @grant        none
// ==/UserScript==
/* jshint -W097 */
'use strict';

// Your code here...

function parseCardsStr(str){
    var patt = /^(.*?)(((\d*\.)?\d+)\s*\/\s*)?((\d*\.)?\d+)\s*$/;
    var matches = patt.exec(str);
    var result = {};
    
    result["task"] = (matches[1] || "").trim();
    result["actual"] = parseFloat(matches[3] || "0");
    result["plan"] = parseFloat(matches[5] || "0");
    
    //alert("main: #" + matches[1] + "# left: #" + matches[3] + "# right: #" + matches[5] + "#" );
    
    //alert(JSON.stringify(result));
    return result;
}

function post(path, parameters) {
    var form = $('<form></form>');

    form.attr("method", "post");
    form.attr("target", "_blank");
    form.attr("action", path);

    $.each(parameters, function(key, value) {
        var field = $('<input></input>');

        field.attr("type", "hidden");
        field.attr("name", key);
        field.attr("value", value);

        form.append(field);
    });

    // The form needs to be a part of the document in
    // order for us to be able to submit it.
    $(document.body).append(form);
    form.submit();
}

function getData(){
    var out = "";
    var analysisRange = ["Todo","<-- current    later-->"];
    var isStart = false;
    var listsResults = [];

    $(".js-list-content").each(function(){
        var listName = $(this).find("div.list-header h2.list-header-name-assist").first().text();
        
        if(!isStart){
            if(listName === analysisRange[0]){
                isStart = true;
            }
        } else {
            if(listName === analysisRange[1]){
                isStart = false;
            }
        }   

        if(isStart){
            out = out + "\n\n--" + listName + "--\n";
            var cards = $(this).find("div.list-cards div.list-card")
            var isStartCard = true;

            var cardsResults = [];

            cards.each(function(){
                var cardName = "";
                $(this).find("a.list-card-title").first().contents().each(function(){
                    if(this.nodeType === 3){
                        cardName = $(this).text().trim();
                    }
                });
                
                var labels = [];
                $(this).find("span.card-label").each(function(){
                    labels.push($(this).text());
                });

                if( cardName.substring(0, 3) === "***"){
                    isStartCard = false;
                }

                // start to parse each card here
                if(isStartCard){
                    var card = parseCardsStr(cardName);
                    card["labels"] = labels;
                    cardsResults.push(card);
                    out = out + JSON.stringify(card) + "\n";
                }
            });

            var listsResult = {};
            listsResult['name'] = listName;
            listsResult['cards'] = cardsResults;
            listsResults.push(listsResult);
        }
    })
    //alert(out);
    return listsResults;
}

window.onload = function(){
    $("div.header-user").prepend("<a class='header-btn js-open-analysis' href='#' aria-label='Create Board or Organization'>    <span class='header-btn-icon icon-lg icon-search light'/></a>");
    $("a.js-open-analysis").click(function(){
        //alert(JSON.stringify(getData()));
        //$("head").append(JSON.stringify(getData()));
        
        post("http://circ.app/trello/save-tasks", {data: JSON.stringify(getData())});
        
    });
    
   
};