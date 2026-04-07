(ql:quickload :hunchentoot :silent t)

(defpackage :singularity.http
  (:use :cl)
  (:export :start :stop))

(in-package :singularity.http)

(load "modules/yggdrasil.lisp")
(load "modules/ipfs.lisp")

(defvar *acceptor* nil)

(defun log-msg (msg)
  (format t "~A [HTTP] ~A~%" (get-universal-time) msg)
  (finish-output))

(defun build-status ()
  (format nil "~:(~{~(~A~)~^~%  ~}~)~%"
    (list
      (cons :yggdrasil-running (singularity.modules.yggdrasil:check-process))
      (cons :yggdrasil-ipv6 (singularity.modules.yggdrasil:get-ipv6))
      (cons :ipfs-running (singularity.modules.ipfs:check-process))
      (cons :ipfs-peer-id (singularity.modules.ipfs:get-peer-id)))))

(hunchentoot:define-easy-handler (status :uri "/status") ()
  (setf (hunchentoot:content-type*) "text/plain; charset=utf-8")
  (build-status))

(hunchentoot:define-easy-handler (index :uri "/") ()
  (setf (hunchentoot:content-type*) "text/html")
  "<h1>OK</h1><a href='/status'>/status</a>")

(defun start (&key (port 49406))
  (setf *acceptor* (make-instance 'hunchentoot:easy-acceptor :port port))
  (hunchentoot:start *acceptor*)
  (log-msg (format nil "Server started on port ~D" port)))

(defun stop ()
  (when *acceptor*
    (hunchentoot:stop *acceptor*)
    (setf *acceptor* nil))
  (log-msg "Server stopped"))
