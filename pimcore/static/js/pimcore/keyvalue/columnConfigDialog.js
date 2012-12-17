pimcore.registerNS("pimcore.keyvalue.columnConfigDialog");

pimcore.keyvalue.columnConfigDialog = Class.create({

    keysAdded: 0,
    requestIsPending: false,

    getConfigDialog: function(node, selectionPanel) {
        this.node = node;
        this.selectionPanel = selectionPanel;

        var selectionWindow = new pimcore.keyvalue.selectionwindow(this);
        selectionWindow.show();
    },


    handleSelectionWindowClosed: function() {
        if (this.keysAdded == 0 && !this.requestIsPending) {
            // no keys added, remove the node
            this.node.remove();
        }
    },

    requestPending: function() {
        this.requestIsPending = true;
    },

    handleAddKeys: function (response) {
        var data = Ext.decode(response.responseText);

        var originalKey =  this.node.attributes.key;

        if(data && data.success) {
            for (var i=0; i < data.data.length; i++) {
                var keyDef = data.data[i];

                var encodedKey = "~keyvalue~" + originalKey + "~" +  keyDef.id;

                if (this.selectionPanel.getRootNode().findChild("key", encodedKey)) {
                    // key already exists, continue
                    continue;
                }

                if (this.keysAdded > 0) {
                    var configEncoded = Ext.encode(this.node.attributes);
                    var configDecoded = Ext.decode(configEncoded);

                    var copy = new Ext.tree.TreeNode( // copy it
                        Ext.apply({}, configDecoded)
                    );
                    this.node = copy;
                    delete this.node.attributes.layout.options;
                    delete this.node.attributes.layout.gridType;
                }


                this.node.attributes.key = encodedKey;
                this.node.attributes.layout.gridType = keyDef.type;

                //TODo  implement all subtypes
                if (keyDef.type == "select") {
                    this.node.attributes.layout.options = Ext.decode(keyDef.possiblevalues);
                }

                this.node.setText( "#" + keyDef.name);

                if (this.keysAdded > 0) {
                    this.selectionPanel.getRootNode().appendChild(this.node);
                }
                this.keysAdded++;
            }
        }

        if (this.keysAdded == 0) {
             this.node.remove();
        }
    }

});