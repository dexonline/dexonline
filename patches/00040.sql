update Definition set internalRep=replace(internalRep,'. o ','. ** '), htmlRep=replace(htmlRep,'. o ','. ♦ ') where internalRep like '%. o %' and sourceId=21 and status=0;
