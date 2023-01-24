module.exports = function(grunt) {

    var baseDir = 'src/skin/frontend/base/default';
    var baseDirJs = baseDir+'/js/easycredit'; 
    var baseDirCss = baseDir+'/css/easycredit';

    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');

    grunt.initConfig({
      uglify: {
        easycredit: {
          options: {
            sourceMap: true,
            sourceMapName: baseDir+'/js/easycredit.min.js.map',
            beautify: false,
            mangle: true,
            compress: true
          },
          files: {
            [baseDirJs+'/easycredit.min.js']: [
                baseDirJs+'/src/easycredit-frontend.js'
            ]
          }
        }
      },
      cssmin: {
          options: {
            mergeIntoShorthands: false,
            roundingPrecision: -1
          },
          easycredit: {
            files: {
              [baseDirCss+'/easycredit.min.css']: [
                baseDirCss+'/src/easycredit-modal.css',
                baseDirCss+'/src/easycredit-widget.css',
                baseDirCss+'/src/easycredit-frontend.css'
              ]
            }
          }
      }

    });
    grunt.registerTask('default', ['uglify','cssmin']);
}
