var express = require('express');
var remote = require('./remote/sockets');
var app = express();
var server = app.listen(process.env.PORT || 9000);

app.use(function(request, response, next) {
    response.header("Access-Control-Allow-Origin", "*");
    response.header("Access-Control-Allow-Headers", "Origin, X-Requested-With, Content-Type, Accept");
    next();
});

app.use(express.static('app'));
app.use(express.static('remote'));

app.get('/', function(req, res){
    res.sendFile('app/views/index.html' , { root : __dirname});
});

app.get('/mobile/:id', function(req, res) {
    res.sendFile('app/views/remote.html' , { root : __dirname});
});

remote.run(server);
