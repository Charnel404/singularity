(defpackage :singularity.modules.yggdrasil
  (:use :cl)
  (:export :get-ipv6 :is-running :check-process))

(in-package :singularity.modules.yggdrasil)

(defparameter *admin-socket* "/var/run/yggdrasil.sock")
(defparameter *admin-port* 1901)

(defun split-string (str char)
  (loop for i = 0 then (1+ j)
        as j = (position char str :start i)
        collect (subseq str i j)
        while j))

(defun extract-ipv6 (output)
  (dolist (line (split-string output #\Newline))
    (when (search "IPv6" line)
      (let ((start (search "\"" line :from-end t)))
        (when start
          (return (subseq line (1+ start) (1- (length line)))))))))

(defun check-process ()
  (handler-case
      (zerop (sb-ext:process-exit-code
              (sb-ext:run-program "pgrep" '("-x" "yggdrasil") :wait t :output nil :error nil)))
    (error () nil)))

(defun is-running ()
  (handler-case
      (progn
        (sb-ext:run-program "pgrep" '("-x" "yggdrasil") :wait t :output nil :error nil)
        t)
    (error () nil)))

(defun get-ipv6 ()
  (let ((result (make-string-output-stream)))
    (handler-case
        (sb-ext:run-program "/usr/sbin/ip" '("a") :output result :error result :wait t)
      (error () "error"))
    (let ((output (get-output-stream-string result)))
      (loop for line in (split-string output #\Newline)
            when (and (search "inet6" line)
                      (or (search "200:" line) (search "0200:" line)))
            do (let ((start (search "inet6 " line)))
                 (when start
                   (let* ((start (+ start 6))
                          (end (position #\/ line :start start)))
                     (return (subseq line start (or end (length line)))))))
            finally (return "not found")))))
