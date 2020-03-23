define(['jquery', 'mod_mindmap/mindmap'],
    function($, Mindmap) {
        return {
            Init: function (mindmapid, locked, convert) {
                var mindmapdata;
                $.ajax({
                   async: false,
                   url: "mindmapdata.php?id="+mindmapid+"&convert="+convert,
                   success: function(result){
                       mindmapdata = result; // Load mindmap data
                   }
                });

                var nodes = [
                    {
                        id:"moodle",
                        label:"Moodle",
                        x: 400,
                        y: 370,
                        font: {
                            color: '#ffffff',
                            size: 18,
                        },
                        color: {
                            background: '#ff0000',
                        },
                        widthConstraint: { maximum: 300 },
                        margin: 10,
                        borderWidth: 1,
                        shape: 'box',
                        labelHighlightBold: false
                    }
                ];
                var edges = [];
                var network = null;

                var inputValue = mindmapdata;
                if (inputValue.length > 0) {
                    var inputData = JSON.parse(inputValue);
                    var data = {
                        nodes: getNodeData(inputData),
                        edges: getEdgeData(inputData)
                    }
                } else {
                    var data = {
                        nodes: nodes,
                        edges: edges
                    };
                }

                function clearPopUp() {
                    document.getElementById("savebutton").onclick = null;
                    document.getElementById("cancelbutton").onclick = null;
                    document.getElementById("network-popup").style.display = "none";
                }

                function cancelEdit(callback) {
                    clearPopUp();
                    callback(null);
                }

                function saveData(data, callback) {
                    data.id = document.getElementById("node-id").value;
                    data.label = document.getElementById("node-label").value;
                    data.shape = document.getElementById("node-shape").value;
                    data.font.color = document.getElementById("node-font-color").value;
                    data.color.background = document.getElementById("node-color-background").value;
                    clearPopUp();
                    callback(data);
                }

                function getNodeData(data) {
                    var networkNodes = [];

                    data.forEach(function(elem, index, array) {
                        networkNodes.push({
                            id: elem.id,
                            label: elem.label,
                            shape: (elem.hasOwnProperty('shape') ? elem.shape : 'box'),
                            x: elem.x,
                            y: elem.y,
                            font: {
                                color: (elem.hasOwnProperty('font') ? elem.font.color : '#343434')
                            },
                            color: {
                                background: (elem.hasOwnProperty('color') ? elem.color.background : '#97c1fc')
                            },
                            widthConstraint: { maximum: 300 },
                            margin: 10
                        });
                    });

                    return new vis.DataSet(networkNodes);
                }

                function getNodeById(data, id) {
                    for (var n = 0; n < data.length; n++) {
                        if (data[n].id == id) {  // double equals since id can be numeric or string
                            return data[n];
                        }
                    };

                    throw 'Can not find id \'' + id + '\' in data';
                }

                function getEdgeData(data) {
                    var networkEdges = [];
                    data.forEach(function(node) {
                        // add the connection
                        node.connections.forEach(function(connId, cIndex, conns) {
                            networkEdges.push({from: node.id, to: connId, width:2});
                            var cNode = getNodeById(data, connId);

                            var elementConnections = cNode.connections;

                            // remove the connection from the other node to prevent duplicate connections
                            var duplicateIndex = elementConnections.filter(function(connection) {
                                return connection == node.id; // double equals since id can be numeric or string
                            })[0];


                            if (duplicateIndex != -1) {
                                elementConnections.splice(duplicateIndex, 1);
                            };
                        });
                    });

                    return new vis.DataSet(networkEdges);
                }

                function objectToArray(obj) {

                    return Object.keys(obj).map(function (key) {
                        obj[key].id = key;
                        return obj[key];
                    });
                }

                function addConnections(elem, index) {
                    // need to replace this with a tree of the network, then get child direct children of the element
                    elem.connections = network.getConnectedNodes(elem.id);
                }

                function addNodeProperties(elem, index) {
                    elem.label = network.body.nodes[elem.id].options.label;
                    if (network.body.nodes[elem.id].options.hasOwnProperty('shape')) {
                        elem.shape = network.body.nodes[elem.id].options.shape;
                    }
                    if (network.body.nodes[elem.id].options.font.hasOwnProperty('color')) {
                        elem.font = {};
                        elem.font.color = network.body.nodes[elem.id].options.font.color;
                    }
                    if (network.body.nodes[elem.id].options.color.hasOwnProperty('background')) {
                        elem.color = {};
                        elem.color.background = network.body.nodes[elem.id].options.color.background;
                    }
                }

                function exportNetwork() {
                    var nodes = objectToArray(network.getPositions());
                    nodes.forEach(addNodeProperties);
                    nodes.forEach(addConnections);

                    // pretty print node data
                    var exportValue = JSON.stringify(nodes, undefined, 2);
                    var ajax = new Mindmap();
                    ajax.mindmapsubmit(mindmapid, exportValue);
                    //console.log(exportValue);
                }

                // create a network
                var container = document.querySelector('.network');
                var options = {};

                // IF editing enabled and no lock active..
                if (locked == 0) {
                    options = {
                        manipulation: {
                            initiallyActive: true,
                            addNode: function(data, callback) {
                                // filling in the popup DOM elements
                                document.getElementById("operation").innerHTML = "Add Node";
                                document.getElementById("node-id").value = data.id;
                                document.getElementById("node-label").value = '';
                                document.getElementById("node-font-color").value = '#343434';
                                document.getElementById("node-color-background").value = '#97c1fc';
                                document.getElementById("node-shape").value = 'box';
                                var newData = {
                                    id: document.getElementById("node-id").value,
                                    x: data.x,
                                    y: data.y,
                                    label: document.getElementById("node-label").value,
                                    shape: document.getElementById("node-shape").value,
                                    font: {
                                        color: document.getElementById("node-font-color").value
                                    },
                                    color: {
                                        background: document.getElementById("node-color-background").value
                                    }
                                };
                                document.getElementById("savebutton").onclick = saveData.bind(
                                    this,
                                    newData,
                                    callback
                                );
                                document.getElementById("cancelbutton").onclick = clearPopUp.bind();
                                document.getElementById("network-popup").style.display = "block";
                            },
                            editNode: function(data, callback) {
                                // filling in the popup DOM elements
                                document.getElementById("operation").innerHTML = "Edit Node";
                                document.getElementById("node-id").value = data.id;
                                document.getElementById("node-label").value = data.label;
                                document.getElementById("node-font-color").value = (data.font.hasOwnProperty('color') ? data.font.color : '#343434' );
                                document.getElementById("node-color-background").value = (data.color.hasOwnProperty('background') ? data.color.background : '#97c1fc');
                                document.getElementById("node-shape").value = (data.hasOwnProperty('shape')  ? data.shape : 'box');
                                document.getElementById("savebutton").onclick = saveData.bind(
                                    this,
                                    data,
                                    callback
                                );
                                document.getElementById("cancelbutton").onclick = cancelEdit.bind(
                                    this,
                                    callback
                                );
                                document.getElementById("network-popup").style.display = "block";
                            },
                            addEdge: function(data, callback) {
                                if (data.from == data.to) {
                                    var r = confirm("Do you want to connect the node to itself?");
                                    if (r == true) {
                                        callback(data);
                                    }
                                } else {
                                    callback(data);
                                }
                            },
                        },
                        physics: {
                            enabled: false,
                        },
                        edges: {
                            physics: false,
                            dashes: false,
                            smooth: {
                                enabled: false,
                            },
                            width: 2,
                        },
                        nodes: {
                            borderWidth: 1,
                            shape: 'box',
                            widthConstraint: { maximum: 300 },
                            margin: 10,
                            font: {
                                size: 18,
                            },
                            labelHighlightBold: false
                        },
                    };

                    $('#export_button').on('click', function(){
                        exportNetwork();
                    });
                } else {
                    options = {
                        physics: {
                            enabled: false,
                        },
                        edges: {
                            physics: false,
                            dashes: false,
                            smooth: {
                                enabled: false,
                            },
                            width: 2,
                        },
                        nodes: {
                            borderWidth: 1,
                            shape: 'box',
                            widthConstraint: { maximum: 300 },
                            margin: 10,
                            font: {
                                size: 18,
                            },
                            labelHighlightBold: false
                        }
                    };
                    if (convert == 1) {
                        $('#export_button').on('click', function(){
                            exportNetwork();
                        });
                    }
                }

                network = new vis.Network(container, data, options);

                if (locked == 0) {
                    network.on("doubleClick", function (params) {
                        if ((params.edges.length >= 0) && (params.nodes.length > 0)) {
                            network.editNode();
                        } else if ((params.edges.length > 0) && (params.nodes.length == 0)) {
                            network.editEdgeMode();
                        } else if ((params.edges.length == 0) && (params.nodes.length == 0)) {
                            network.addEdgeMode();
                        }
                    });
                }
            }
    };
});
