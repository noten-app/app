const subject_selector = document.getElementById('subject-selector');
const task_input = document.getElementById('task-input');
const date_input_input = document.getElementById('date_input-input');
const subject_edit_button = document.getElementById('task_save');
const task_mark_undone_button = document.getElementById('task_mark_undone');
const task_delete_button = document.getElementById('task_delete');

subject_edit_button.addEventListener('click', () => {
    // check if task is empty
    if (task_input.value == "") {
        task_input.style.border = "1px solid red";
        return;
    } else {
        task_input.style.border = "";
    }
    $.ajax({
        url: './edit.php',
        type: 'POST',
        data: {
            subject: subject_selector.value,
            type: type,
            task_id: task_id,
            date_due: date_input_input.value,
            task: task_input.value
        },
        success: (data) => {
            if (data == "success") {
                location.assign("/homework");
            } else {
                console.log(data);
            }
        }
    });
});

task_mark_undone_button.addEventListener('click', () => {
    $.ajax({
        url: './state.php',
        type: 'POST',
        data: {
            status: 2,
            task_id: task_id
        },
        success: (data) => {
            console.log(data);
            if (data != "success") console.log(data);
            else $.ajax({
                url: './edit.php',
                type: 'POST',
                data: {
                    subject: subject_selector.value,
                    type: type,
                    task_id: task_id,
                    date_due: date_input_input.value,
                    task: task_input.value
                },
                success: (data) => {
                    if (data == "success") {
                        location.assign("/homework");
                    } else {
                        console.log(data);
                    }
                }
            });
        }
    });
});

task_delete_button.addEventListener('click', () => {
    $.ajax({
        url: './delete.php',
        type: 'POST',
        data: {
            task_id: task_id
        },
        success: (data) => {
            if (data == "success") {
                location.assign("/homework");
            } else {
                console.log(data);
            }
        }
    });
});