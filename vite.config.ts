import { createAppConfig } from '@nextcloud/vite-config'
import { visualizer } from 'rollup-plugin-visualizer'
import { join, resolve } from 'node:path'
import tailwindcss from '@tailwindcss/vite'

export default createAppConfig(
	{
		main: resolve(join('src', 'main.js')),
	},
	{
		createEmptyCSSEntryPoints: true,
		config: {
			define: {
				appName: JSON.stringify('kinship'),
				appVersion: '1',
			},
			build: {
				sourcemap: false,
			},
			server: {
				host: '127.0.0.1',
				port: 5173,
				cors: true,
				strictPort: true,
			},
			plugins: [
				tailwindcss(),
			],
			resolve: {
				alias: {
					'@': resolve(join('src')),
				},
			},
		},
	},
)
