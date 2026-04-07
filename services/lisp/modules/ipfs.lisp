(defpackage :singularity.modules.ipfs
  (:use :cl)
  (:export :get-peer-id :get-addresses :is-running :check-process))

(in-package :singularity.modules.ipfs)

(defparameter *ipfs-bin* "/opt/IPFS Desktop/resources/app.asar.unpacked/node_modules/kubo/kubo/ipfs")

(defun check-process ()
  (handler-case
      (zerop (sb-ext:process-exit-code
              (sb-ext:run-program "pgrep" '("-x" "ipfs-desktop") :wait t :output nil :error nil)))
    (error () nil)))

(defun is-running ()
  (handler-case
      (progn
        (sb-ext:run-program "pgrep" '("-x" "ipfs-desktop") :wait t :output nil :error nil)
        t)
    (error () nil)))

(defun get-peer-id ()
  (let ((result (make-string-output-stream)))
    (handler-case
        (sb-ext:run-program *ipfs-bin* '("id" "-f" "<id>") 
                          :output result :error result :wait t)
      (error () "error"))
    (let* ((output (get-output-stream-string result))
           (trimmed (string-trim '(#\Newline #\Space #\Tab) output)))
      (if (or (string= "" trimmed) (search "error" trimmed :test #'char-equal))
          "not found"
          trimmed))))

(defun get-addresses ()
  (let ((result (make-string-output-stream)))
    (handler-case
        (sb-ext:run-program "/usr/bin/curl" '("-s" "http://127.0.0.1:5001/api/v0/id") 
                          :output result :error result :wait t)
      (error () "error"))
    (let* ((output (get-output-stream-string result))
           (start (search "\"Addresses\":" output))
           (bracket-start (position #\[ output :start (or start 0)))
           (bracket-end (position #\] output :start (or bracket-start 0))))
      (if (and bracket-start bracket-end)
          (subseq output (1+ bracket-start) bracket-end)
          ""))))
