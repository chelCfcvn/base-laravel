module.exports = {
    apps: [
        {
            name: 'pawn_shop_dev',
            exec_mode: 'cluster',
            instances: 1,
            script: 'laravel-echo-server',
            args: 'start'
        }
    ]
}
