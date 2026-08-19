const { Client } = require('ssh2');

const config = {
    host: '147.93.23.184',
    port: 65002,
    username: 'u406313474',
    password: 'Gaurav@20221',
};

const targetDir = '/home/u406313474/domains/sma.digitalrubix.site/public_html';

const htaccessContent = `<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^$ public/ [L]
    RewriteRule (.*) public/$1 [L]
</IfModule>`;

const conn = new Client();

conn.on('ready', () => {
    console.log('Writing root .htaccess for domain root routing...');
    const cmd = `cat << 'EOF' > ${targetDir}/.htaccess\n${htaccessContent}\nEOF`;

    conn.exec(cmd, (err, stream) => {
        if (err) throw err;
        stream.on('close', () => {
            console.log('Root .htaccess created successfully!');
            conn.end();
        });
    });
}).connect(config);
