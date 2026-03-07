a:13:{i:0;a:3:{i:0;s:14:"document_start";i:1;a:0:{}i:2;i:0;}i:1;a:3:{i:0;s:6:"p_open";i:1;a:0:{}i:2;i:0;}i:2;a:3:{i:0;s:6:"plugin";i:1;a:4:{i:0;s:22:"markdowku_boldasterisk";i:1;a:2:{i:0;i:1;i:1;s:2:"**";}i:2;i:1;i:3;s:2:"**";}i:2;i:1;}i:3;a:3:{i:0;s:12:"internallink";i:1;a:2:{i:0;s:8:":MERMAID";i:1;s:20:"MERMAID:ARCHITECTURE";}i:2;i:3;}i:4;a:3:{i:0;s:6:"plugin";i:1;a:4:{i:0;s:22:"markdowku_boldasterisk";i:1;a:2:{i:0;i:4;i:1;s:2:"**";}i:2;i:4;i:3;s:2:"**";}i:2;i:36;}i:5;a:3:{i:0;s:7:"p_close";i:1;a:0:{}i:2;i:38;}i:6;a:3:{i:0;s:6:"p_open";i:1;a:0:{}i:2;i:38;}i:7;a:3:{i:0;s:6:"plugin";i:1;a:4:{i:0;s:7:"mermaid";i:1;a:2:{i:0;i:1;i:1;s:9:"<mermaid>";}i:2;i:1;i:3;s:9:"<mermaid>";}i:2;i:41;}i:8;a:3:{i:0;s:6:"plugin";i:1;a:4:{i:0;s:7:"mermaid";i:1;a:2:{i:0;i:3;i:1;s:276:"
architecture-beta
    group api(cloud)[API]

    service db(database)[Database] in api
    service disk1(disk)[Storage] in api
    service disk2(disk)[Storage] in api
    service server(server)[Server] in api

    db:L -- R:server
    disk1:T -- B:server
    disk2:T -- B:db
";}i:2;i:3;i:3;s:276:"
architecture-beta
    group api(cloud)[API]

    service db(database)[Database] in api
    service disk1(disk)[Storage] in api
    service disk2(disk)[Storage] in api
    service server(server)[Server] in api

    db:L -- R:server
    disk1:T -- B:server
    disk2:T -- B:db
";}i:2;i:50;}i:9;a:3:{i:0;s:6:"plugin";i:1;a:4:{i:0;s:7:"mermaid";i:1;a:2:{i:0;i:4;i:1;s:10:"</mermaid>";}i:2;i:4;i:3;s:10:"</mermaid>";}i:2;i:326;}i:10;a:3:{i:0;s:5:"cdata";i:1;a:1:{i:0;s:0:"";}i:2;i:336;}i:11;a:3:{i:0;s:7:"p_close";i:1;a:0:{}i:2;i:336;}i:12;a:3:{i:0;s:12:"document_end";i:1;a:0:{}i:2;i:336;}}