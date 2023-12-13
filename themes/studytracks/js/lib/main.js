jQuery(document).ready(function($) {

    var Deletions = {
        init: function () {
            Deletions.deleteItem();
            Deletions.BulkChangeClass();
            Deletions.HomeworkPlaylists();
            Deletions.HomeworkPlaylistSearchLessons();
            Deletions.HomeworkPlaylistSearchSelectedLessons();
            Deletions.HomeworkPlaylistSearchQuizzes();
            Deletions.HomeworkPlaylistSearchSelectedQuizzes();
        },

        deleteItem: function() {
          $('.delete').click(function() {

              if (confirm('Are you sure you want to delete?')) {
                  var id = $(this).data('id');
                  var type = $(this).data('type');
                  var uri;
                  if (type === 'class') {
                      uri = '/school/classes/';
                  } else if (type === 'student') {
                      uri = '/school/students/';
                  } else if (type === 'staff') {
                      uri = '/school/staff/';
                  }

                  var fullUri = uri + id + '/delete';

                  $.ajax({
                      url: fullUri,
                      type: 'DELETE',
                      success: function () {
                          window.location.replace(uri);
                      }
                  });
              }
          });
        },

        BulkChangeClass: function(){
            $('#bulk-change-class').click(function(e){
                e.preventDefault();
                $('#students-table').toggleClass('active');
                $('#ClassSelector').toggle();
                $('#students-table .elem').toggle();
                $('#submit-bulk-change-class').toggle();
                $('#bulk-change-class-form')[0].reset();
            });
        },

        HomeworkPlaylists: function() {

            if ($('.override-background').length > 0) {
                $('.main').css('background-color', 'transparent');
            }

            $('.homework-playlist-save').click(function(){
                if($('.HomeworkPlaylistTitle').val().length === 0){
                    alert('You need to add a title to this homework playlist before you can save it!');
                    return false;
                }
            });

            //$(".container-homework-playlists .row-expand").click(function () {
            //
            //    $(this).find('img').toggleClass('rotated');
            //    $(this).parents('.row').next('.container-homework-playlist-lessons').toggle();
            //});

            $(".edit .lesson-container .lesson").mousedown(function () {

                if ($(this).find('.lesson_input').attr('name')) {
                    $(this).find('.lesson_input').removeAttr('name');
                } else {
                    $(this).find('.lesson_input').attr('name', 'lessonID[]');
                }
                $(this).find('.fa').toggleClass('fa-check').toggleClass('fa-check-circle');
            });

            $(".edit .quiz-container .lesson").mousedown(function () {

                if ($(this).find('.lesson_input').attr('name')) {
                    $(this).find('.lesson_input').removeAttr('name');
                } else {
                    $(this).find('.lesson_input').attr('name', 'quizID[]');
                }
                $(this).find('.fa').toggleClass('fa-check').toggleClass('fa-check-circle');
            });

            $(".edit .selected-lesson-container .lesson").mousedown(function() {

                if ($(this).find('.lesson_input').attr('name')) {
                    $(this).find('.lesson_input').removeAttr('name');
                } else {
                    $(this).find('.lesson_input').attr('name', 'removeLessonID[]');
                }
                $(this).find('.fa').toggleClass('fa-check').toggleClass('fa-times-circle');
            });

            $(".edit .selected-quiz-container .lesson").mousedown(function() {

                if ($(this).find('.lesson_input').attr('name')) {
                    $(this).find('.lesson_input').removeAttr('name');
                } else {
                $(this).find('.lesson_input').attr('name', 'removeQuizID[]');
                }
                $(this).find('.fa').toggleClass('fa-check').toggleClass('fa-times-circle');
            });
        },

        HomeworkPlaylistSearchLessons: function(){

            var timeoutID = null,
                origin = window.location.origin,
                TrackId = $('.searchLessons').attr("data-id"),
                TrackUrl = '';

            if (TrackId) {
                TrackUrl = origin + '/school/homework/filterTracks/' + TrackId;
            } else {
                TrackUrl = origin + '/school/homework/filterTracks';
            }

            function findLesson(str) {
                $.ajax({
                    url: TrackUrl,
                    method: 'GET',
                    data: {string: str},
                    success: function (data) {
                        //$('.lesson-container div').remove();
                        var items = $(data);
                        $('.lesson-container').append(items);
                    }
                });
            }

            $('.searchLessons').keyup(function (e) {

                $('.lesson-container div').remove();
                clearTimeout(timeoutID);
                timeoutID = setTimeout(findLesson.bind(undefined, e.target.value), 300);
            });
        },

        HomeworkPlaylistSearchSelectedLessons: function(){

            var timeoutID = null,
                origin = window.location.origin,
                SelectedTrackID = $('.searchSelectedLessons').attr("data-id"),
                SelectedTrackUrl = '';

            if (SelectedTrackID) {
                SelectedTrackUrl = origin + '/school/homework/filterSelectedTracks/' + SelectedTrackID;
            } else {
                SelectedTrackUrl = origin + '/school/homework/filterSelectedTracks';
            }

            function findSelectedLesson(str) {
                $.ajax({
                    url: SelectedTrackUrl,
                    method: 'GET',
                    data: {string: str},
                    success: function (data) {
                        //$('#selected-lesson-container div').remove();
                        var items = $(data);
                        $('.selected-lesson-container').append(items);
                    }
                });
            }

            $('.searchSelectedLessons').keyup(function (e) {

                $('.selected-lesson-container div').remove();
                clearTimeout(timeoutID);
                timeoutID = setTimeout(findSelectedLesson.bind(undefined, e.target.value), 300);
            });
        },

        HomeworkPlaylistSearchQuizzes: function () {

            var timeoutID = null,
                origin = window.location.origin,
                QuizId = $('.searchQuizzes').attr("data-id"),
                QuizUrl = '';

            if (QuizId) {
                QuizUrl = origin + '/school/homework/filterQuizzes/' + QuizId;
            } else {
                QuizUrl = origin + '/school/homework/filterQuizzes';
            }

            function findQuiz(str) {
                console.log('search: ' + str);

                $.ajax({
                    url: QuizUrl,
                    method: 'GET',
                    data: {string: str},
                    success: function (data) {
                        $('.quiz-container div').remove();
                        var items = $(data);
                        $('.quiz-container').append(items);
                    }
                });
            }

            $('.searchQuizzes').keyup(function (e) {
                $('.quiz-container div').remove();
                clearTimeout(timeoutID);
                timeoutID = setTimeout(findQuiz.bind(undefined, e.target.value), 500);
            });
        },

        HomeworkPlaylistSearchSelectedQuizzes: function(){

            var timeoutID = null,
                origin = window.location.origin,
                SelectedTrackID = $('.searchSelectedLessons').attr("data-id"),
                SelectedTrackUrl = '',
                SelectedQuizID = $('.searchSelectedQuizzes').attr("data-id"),
                SelectedQuizUrl = '';

            if (SelectedQuizID) {
                SelectedQuizUrl = origin + '/school/homework/filterSelectedQuizzes/' + SelectedQuizID;
            } else {
                SelectedQuizUrl = origin + '/school/homework/filterSelectedQuizzes';
            }

            function findSelectedQuiz(str) {
                $.ajax({
                    url: SelectedQuizUrl,
                    method: 'GET',
                    data: {string: str},
                    success: function (data) {
                        var items = $(data);
                        $('.selected-quiz-container').append(items);
                    }
                });
            }

            $('.searchSelectedQuizzes').keyup(function (e) {

                $('.selected-quiz-container div').remove();
                clearTimeout(timeoutID);
                timeoutID = setTimeout(findSelectedQuiz.bind(undefined, e.target.value), 300);
            });
        }
    };

    Deletions.init();

});