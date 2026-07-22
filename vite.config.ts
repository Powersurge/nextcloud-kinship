import { createAppConfig } from '@nextcloud/vite-config'
import { visualizer } from 'rollup-plugin-visualizer'
import { join, resolve } from 'node:path'

export default createAppConfig(
	{
		main: resolve(join('src', 'main.js')),
	},
	{
		createEmptyCSSEntryPoints: true,
		config: {
			build: {
				sourcemap: false,
			},
			plugins: [
				visualizer({
					filename: 'stats.html',
					gzipSize: true,
					brotliSize: true,
				}),
			],
			resolve: {
				alias: {
					'@': resolve(join('src')),
					'@components': resolve(join('src', 'components')),
				},
			},
		},
	},
)
