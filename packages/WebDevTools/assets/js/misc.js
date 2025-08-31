if (typeof window.console == 'undefined') {
    var console = {
        log: function (str) {},
    };
}
String.prototype.toCamelCase = function () {
    return this.toLowerCase().replace(/-(.)/g, function (match, group1) {
        return group1.toUpperCase();
    });
};
