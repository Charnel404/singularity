(defun exec-program (choice)
    (format t "Выбрано: ~A~%" choice))

(format t "Выберите программу~%")
(finish-output)
(let ((choice (parse-integer (read-line))))
    (exec-program choice)
    (let ((programs '(
        ; 0. “Hello + ввод имени”
        (
            (format t "Hello, ~A!~%" (read-line))
            (finish-output)
        )
        ; 1. Калькулятор 2 чисел
        (
            (format t "Enter first number: ")
                (finish-output)
                (let ((num1 (parse-integer (read-line))))
                    (format t "Enter operator: ")
                    (finish-output)
                    (let ((operator (read-line)))
                        (format t "Enter second number: ")
                        (finish-output)
                        (let ((num2 (parse-integer (read-line))))
                            (format t "The ~A of ~A and ~A is ~A~%" operator num1 num2 (cond
                                ((string= operator "+") (+ num1 num2))
                                ((string= operator "-") (- num1 num2))
                                ((string= operator "*") (* num1 num2))
                                ((string= operator "/") (/ num1 num2)))))))
        )
        ; 2. Чётное / нечётное
        (
            (format t "Enter number: ")
                (finish-output)
                (let ((num (parse-integer (read-line))))
                    (if (evenp num) (format t "~A is even" num) (format t "~A is odd" num)))
        )
        ; 3. Запись в файл
        (
            (with-open-file (out "program.lisp"
                     :direction :output
                     :if-exists :supersede)
            (print '(format t "~A~%" (+ 1 2)) out))
        )
        ; 4. Чтение и исполнение из файла
        (
            (eval
            (with-open-file (in "program.lisp")
                (read in)))
        )
    )))
        (format t "You chose option ~A~%" choice)
           (eval `(progn ,@(nth choice programs)))
    ))

