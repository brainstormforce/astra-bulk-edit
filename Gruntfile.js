module.exports = function( grunt ) {

	'use strict';

	var pkgInfo = grunt.file.readJSON('package.json');

	// Project configuration
	grunt.initConfig( {

		pkg: grunt.file.readJSON( 'package.json' ),

		addtextdomain: {
			options: {
				textdomain: 'astra-bulk-edit',
			},
			target: {
				files: {
					src: [ 
						'*.php',
							'**/*.php',
							'!\.git/**/*',
							'!bin/**/*',
							'!node_modules/**/*',
							'!tests/**/*'
					]
				}
			}
		},

		wp_readme_to_markdown: {
			your_target: {
				files: {
					'README.md': 'readme.txt'
				}
			},
		},

		makepot: {
			target: {
				options: {
					domainPath: '/languages',
					exclude: [ '\.git/*', 'bin/*', 'node_modules/*', 'tests/*' ],
					mainFile: 'astra-bulk-edit.php',
					potFilename: 'astra-bulk-edit.pot',
					potHeaders: {
						poedit: true,
						'x-poedit-keywordslist': true
					},
					type: 'wp-plugin',
					updateTimestamp: true
				}
			}
		},

		copy: {
			main: {
				options: {
					mode: true
				},
				src: [
					'**',
					'!node_modules/**',
					'!build/**',
					'!css/sourcemap/**',
					'!.git/**',
					'!bin/**',
					'!.gitlab-ci.yml',
					'!bin/**',
					'!tests/**',
					'!phpunit.xml.dist',
					'!*.sh',
					'!*.map',
					'!Gruntfile.js',
					'!package.json',
					'!.gitignore',
					'!phpunit.xml',
					'!README.md',
					'!sass/**',
					'!codesniffer.ruleset.xml',
					'!vendor/**',
					'!composer.json',
					'!composer.lock',
					'!package-lock.json',
					'!phpcs.xml.dist',
				],
				dest: 'astra-bulk-edit/'
			}
		},

		compress: {
			main: {
				options: {
					archive: 'astra-bulk-edit-' + pkgInfo.version + '.zip',
					mode: 'zip'
				},
				files: [
					{
						src: [
							'./astra-bulk-edit/**'
						]

					}
				]
			}
		},

		clean: {
			main: ["astra-bulk-edit"],
			zip: ["*.zip"]

		},

		bumpup: {
			options: {
				updateProps: {
					pkg: 'package.json'
				}
			},
			file: 'package.json'
		},

		replace: {
			plugin_main: {
				src: ['astra-bulk-edit.php'],
				overwrite: true,
				replacements: [
					{
						from: /Version: \bv?(?:0|[1-9]\d*)\.(?:0|[1-9]\d*)\.(?:0|[1-9]\d*)(?:-[\da-z-A-Z-]+(?:\.[\da-z-A-Z-]+)*)?(?:\+[\da-z-A-Z-]+(?:\.[\da-z-A-Z-]+)*)?\b/g,
						to: 'Version: <%= pkg.version %>'
					}
				]
			},
			plugin_const: {
				src: ['astra-bulk-edit.php'],
				overwrite: true,
				replacements: [
					{
						from: /ASTRA_BLK_VER', '.*?'/g,
						to: 'ASTRA_BLK_VER\', \'<%= pkg.version %>\''
					}
				]
			},
			plugin_function_comment: {
				src: [
					'*.php',
					'**/*.php',
					'!node_modules/**',
					'!php-tests/**',
					'!bin/**'
				],
				overwrite: true,
				replacements: [
					{
						from: 'x.x.x',
						to: '<%=pkg.version %>'
					}
				]
			}
		}

	} );

	grunt.loadNpmTasks( 'grunt-wp-i18n' );
	grunt.loadNpmTasks( 'grunt-wp-readme-to-markdown' );
	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-contrib-compress');
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-bumpup');
	grunt.loadNpmTasks('grunt-text-replace');

	grunt.registerTask('i18n', ['addtextdomain', 'makepot']);
	grunt.registerTask('readme', ['wp_readme_to_markdown']);

	// Grunt release - Create installable package of the local files
	grunt.registerTask('release', ['clean:zip', 'copy:main', 'compress:main', 'clean:main']);

	// Bump Version - `grunt version-bump --ver=<version-number>`
	grunt.registerTask('version-bump', function (ver) {

		var newVersion = grunt.option('ver');

		if (newVersion) {
			newVersion = newVersion ? newVersion : 'patch';

			grunt.task.run('bumpup:' + newVersion);
			grunt.task.run('replace');
		}
	});

	grunt.util.linefeed = '\n';

};
