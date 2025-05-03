// start.js
const { spawn } = require('child_process');

function startServer() {
    const child = spawn('node', ['server.js'], {
        stdio: 'inherit',
        shell: true,
    });

    child.on('exit', (code) => {
        if (code === 100) {
            console.log('🔁 Restarting server...');
            startServer();
        } else {
            console.log('❌ Server stopped with code', code);
        }
    });
}

startServer();
