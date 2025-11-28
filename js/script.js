$(document).ready(function() {
    
    // Toggle Sidebar
    $('#menuToggle').click(function() {
        $('#sidebar').toggleClass('collapsed');
        $('.main-content').toggleClass('expanded');
    });

    // Responsive sidebar for mobile
    if ($(window).width() <= 768) {
        $('#sidebar').addClass('collapsed');
        $('.main-content').addClass('expanded');
        
        $('#menuToggle').click(function() {
            $('#sidebar').toggleClass('active');
        });
        
        // Close sidebar when clicking outside on mobile
        $(document).click(function(e) {
            if ($(window).width() <= 768) {
                if (!$(e.target).closest('#sidebar, #menuToggle').length) {
                    $('#sidebar').removeClass('active');
                }
            }
        });
    }

    // Handle window resize
    $(window).resize(function() {
        if ($(window).width() <= 768) {
            $('#sidebar').addClass('collapsed').removeClass('active');
            $('.main-content').addClass('expanded');
        } else {
            $('#sidebar').removeClass('collapsed active');
            $('.main-content').removeClass('expanded');
        }
    });

    // Modal functionality
    $('.modal-trigger').click(function(e) {
        e.preventDefault();
        var modalId = $(this).data('modal');
        $('#' + modalId).fadeIn(300);
    });

    // Close modal
    $('.close').click(function() {
        $(this).closest('.modal').fadeOut(300);
    });

    // Close modal when clicking outside
    $('.modal').click(function(e) {
        if (e.target === this) {
            $(this).fadeOut(300);
        }
    });

    // Close modal on ESC key
    $(document).keyup(function(e) {
        if (e.key === "Escape") {
            $('.modal').fadeOut(300);
        }
    });

    // Form validation
    $('form').submit(function(e) {
        var isValid = true;
        var emptyFields = [];
        
        $(this).find('input[required], select[required], textarea[required]').each(function() {
            if ($(this).val() === '' || $(this).val() === null) {
                isValid = false;
                $(this).css('border-color', '#c41e3a');
                emptyFields.push($(this).attr('name') || 'Unknown field');
            } else {
                $(this).css('border-color', '#ffc0cb');
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields!\n\nMissing: ' + emptyFields.join(', '));
            return false;
        }
    });

    // Reset border color on input
    $('input, select, textarea').on('focus', function() {
        $(this).css('border-color', '#ff6b9d');
    });

    $('input, select, textarea').on('blur', function() {
        if ($(this).val() !== '') {
            $(this).css('border-color', '#ffc0cb');
        }
    });

    // Delete confirmation
    $('.btn-delete').click(function(e) {
        var confirmed = confirm('Are you sure you want to delete this item?\n\nThis action cannot be undone!');
        if (!confirmed) {
            e.preventDefault();
            return false;
        }
    });

    // Attendance checkbox styling and row highlighting
    $('.attendance-checkbox').change(function() {
        var row = $(this).closest('tr');
        var presenceChecked = row.find('input[name*="[presence]"]').is(':checked');
        var participationChecked = row.find('input[name*="[participation]"]').is(':checked');
        
        if (presenceChecked) {
            row.css('background-color', '#d4edda');
        } else {
            row.css('background-color', '');
        }
        
        if (participationChecked) {
            row.css('font-weight', 'bold');
        } else {
            row.css('font-weight', '');
        }
    });

    // Initialize attendance checkbox states on page load
    $('.attendance-checkbox').each(function() {
        var row = $(this).closest('tr');
        var presenceChecked = row.find('input[name*="[presence]"]').is(':checked');
        var participationChecked = row.find('input[name*="[participation]"]').is(':checked');
        
        if (presenceChecked) {
            row.css('background-color', '#d4edda');
        }
        
        if (participationChecked) {
            row.css('font-weight', 'bold');
        }
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Table search functionality
    $('#tableSearch').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Print functionality
    $('.btn-print').click(function(e) {
        e.preventDefault();
        window.print();
    });

    // Smooth scroll to top
    $('.scroll-top').click(function() {
        $('html, body').animate({scrollTop: 0}, 'slow');
        return false;
    });

    // File input custom styling
    $('input[type="file"]').change(function() {
        var fileName = $(this).val().split('\\').pop();
        var label = $(this).siblings('label');
        
        if (fileName) {
            if (label.length) {
                label.text(fileName);
            } else {
                $(this).after('<label style="color: #ff6b9d; font-size: 12px; margin-top: 5px; display: block;">Selected: ' + fileName + '</label>');
            }
        }
    });

    // Select all checkboxes functionality
    $('#selectAll').change(function() {
        var isChecked = $(this).is(':checked');
        $('.attendance-checkbox').prop('checked', isChecked).trigger('change');
    });

    // Confirm before leaving page with unsaved changes
    var formChanged = false;
    $('form input, form select, form textarea').change(function() {
        formChanged = true;
    });

    $('form').submit(function() {
        formChanged = false;
    });

    $(window).on('beforeunload', function() {
        if (formChanged) {
            return 'You have unsaved changes. Are you sure you want to leave?';
        }
    });

    // Add animation to cards on scroll
    $(window).scroll(function() {
        $('.card').each(function() {
            var elementTop = $(this).offset().top;
            var viewportTop = $(window).scrollTop();
            var viewportBottom = viewportTop + $(window).height();
            
            if (elementTop < viewportBottom) {
                $(this).css('opacity', '1');
            }
        });
    });

    // Highlight active navigation item
    var currentPage = window.location.pathname.split('/').pop();
    $('.nav-item').each(function() {
        var href = $(this).attr('href');
        if (href === currentPage) {
            $(this).addClass('active');
        }
    });

    // Double click protection on submit buttons
    $('form').submit(function() {
        $(this).find('button[type="submit"]').prop('disabled', true);
        $(this).find('button[type="submit"]').html('<i class="fas fa-spinner fa-spin"></i> Processing...');
    });

    // Tooltip functionality (if needed)
    $('[data-tooltip]').hover(
        function() {
            var tooltip = $(this).data('tooltip');
            $(this).append('<div class="tooltip-box">' + tooltip + '</div>');
            $('.tooltip-box').fadeIn(200);
        },
        function() {
            $('.tooltip-box').remove();
        }
    );

    // Auto-submit form on select change (for filters)
    $('.auto-submit').change(function() {
        $(this).closest('form').submit();
    });

    // Number input validation
    $('input[type="number"]').on('keypress', function(e) {
        if (e.which < 48 || e.which > 57) {
            e.preventDefault();
        }
    });

    // Email validation
    $('input[type="email"]').on('blur', function() {
        var email = $(this).val();
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email && !emailRegex.test(email)) {
            $(this).css('border-color', '#c41e3a');
            alert('Please enter a valid email address!');
        } else {
            $(this).css('border-color', '#ffc0cb');
        }
    });

    // Table row click to select
    $('tbody tr').click(function(e) {
        if (!$(e.target).is('a, button, input, select')) {
            $(this).toggleClass('selected');
        }
    });

    // Bulk actions (if needed)
    $('.bulk-action').click(function(e) {
        e.preventDefault();
        var selectedIds = [];
        
        $('tbody tr.selected').each(function() {
            var id = $(this).data('id');
            if (id) {
                selectedIds.push(id);
            }
        });
        
        if (selectedIds.length === 0) {
            alert('Please select at least one item!');
            return false;
        }
        
        console.log('Selected IDs:', selectedIds);
        // Implement bulk action logic here
    });

    // Character counter for textareas
    $('textarea[maxlength]').each(function() {
        var maxLength = $(this).attr('maxlength');
        var currentLength = $(this).val().length;
        $(this).after('<div class="char-counter">' + currentLength + ' / ' + maxLength + '</div>');
    });

    $('textarea[maxlength]').on('keyup', function() {
        var maxLength = $(this).attr('maxlength');
        var currentLength = $(this).val().length;
        $(this).siblings('.char-counter').text(currentLength + ' / ' + maxLength);
    });

    // Loading animation
    function showLoading() {
        $('body').append('<div class="loading-overlay"><div class="spinner"><i class="fas fa-spinner fa-spin"></i></div></div>');
    }

    function hideLoading() {
        $('.loading-overlay').fadeOut(300, function() {
            $(this).remove();
        });
    }

    // AJAX form submission example
    $('.ajax-form').submit(function(e) {
        e.preventDefault();
        
        var form = $(this);
        var formData = new FormData(this);
        
        showLoading();
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                hideLoading();
                alert('Success!');
                location.reload();
            },
            error: function() {
                hideLoading();
                alert('An error occurred. Please try again.');
            }
        });
    });

    // Initialize date picker (if using HTML5 date input)
    $('input[type="date"]').attr('min', new Date().toISOString().split('T')[0]);

    // Prevent multiple form submissions
    var isSubmitting = false;
    $('form').submit(function() {
        if (isSubmitting) {
            return false;
        }
        isSubmitting = true;
        return true;
    });

    console.log('✨ Attendance System Loaded Successfully! ✨');
});

// Additional utility functions
function confirmAction(message) {
    return confirm(message || 'Are you sure you want to perform this action?');
}

function showAlert(message, type) {
    type = type || 'success';
    var alertClass = 'alert-' + type;
    var alertHtml = '<div class="alert ' + alertClass + '">' + message + '</div>';
    
    $('body').prepend(alertHtml);
    
    setTimeout(function() {
        $('.alert').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);
}

// Export functions for global use
window.confirmAction = confirmAction;
window.showAlert = showAlert;