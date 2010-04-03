update Definition set internalRep = replace(internalRep, '//', ''), htmlRep = replace(htmlRep, '//', '') where sourceId = 9;
